<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Generating gif/png-files from TypoScript
 * Used by the menu-objects and imgResource in TypoScript.
 *
 * $Id: class.tslib_gifbuilder.php 3439 2008-03-16 19:16:51Z flyguide $
 * Revised for TYPO3 3.6 June/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  102: class tslib_gifBuilder extends t3lib_stdGraphic
 *  129:     function start($conf,$data)
 *  315:     function gifBuild()
 *  343:     function make()
 *
 *              SECTION: Various helper functions
 *  486:     function checkTextObj($conf)
 *  566:     function calcOffset($string)
 *  615:     function getResource($file,$fileArray)
 *  632:     function checkFile($file)
 *  643:     function fileName($pre)
 *  659:     function extension()
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */





















/**
 * GIFBUILDER extension class.
 * This class allows for advanced rendering of images with various layers of images, text and graphical primitives.
 * The concept is known from TypoScript as "GIFBUILDER" where you can define a "numerical array" (TypoScript term as well) of "GIFBUILDER OBJECTS" (like "TEXT", "IMAGE", etc.) and they will be rendered onto an image one by one.
 * The name "GIFBUILDER" comes from the time where GIF was the only file format supported. PNG is just as well to create today (configured with TYPO3_CONF_VARS[GFX])
 * Not all instances of this class is truely building gif/png files by layers; You may also see the class instantiated for the purpose of using the scaling functions in the parent class, t3lib_stdGraphic.
 *
 * Here is an example of how to use this class (from tslib_content.php, function getImgResource):
 *
 * $gifCreator = t3lib_div::makeInstance('tslib_gifbuilder');
 * $gifCreator->init();
 * $theImage='';
 * if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['gdlib'])	{
 * $gifCreator->start($fileArray,$this->data);
 * $theImage = $gifCreator->gifBuild();
 * }
 * return $gifCreator->getImageDimensions($theImage);
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tslib
 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=377&cHash=e00ac666f3
 */
class tslib_gifBuilder extends t3lib_stdGraphic {

		// Internal
	var $im = '';		// the main image
	var $w = 0;			// the image-width
	var $h = 0;			// the image-height
	var $map;			// map-data
	var $workArea;
	var $setup = Array ();		// This holds the operational setup for gifbuilder. Basically this is a TypoScript array with properties.
	var $combinedTextStrings = array();		// Contains all text strings used on this image
	var $combinedFileNames = array();		// Contains all filenames (basename without extension) used on this image
	var $data = Array();		// This is the array from which data->field: [key] is fetched. So this is the current record!
	var $objBB = Array();
	var $myClassName = 'gifbuilder';
	var $charRangeMap=array();

	/**
	 * Initialization of the GIFBUILDER objects, in particular TEXT and IMAGE. This includes finding the bounding box, setting dimensions and offset values before the actual rendering is started.
	 * Modifies the ->setup, ->objBB internal arrays
	 * Should be called after the ->init() function which initializes the parent class functions/variables in general.
	 * The class tslib_gmenu also uses gifbuilder and here there is an interesting use since the function findLargestDims() from that class calls the init() and start() functions to find the total dimensions before starting the rendering of the images.
	 *
	 * @param	array		TypoScript properties for the GIFBUILDER session. Stored internally in the variable ->setup
	 * @param	array		The current data record from tslib_cObj. Stored internally in the variable ->data
	 * @return	void
	 * @see tslib_cObj::getImgResource(), tslib_gmenu::makeGifs(), tslib_gmenu::findLargestDims()
	 */
	function start($conf,$data)	{

		if (is_array($conf))	{
			$this->setup = $conf;
			$this->data = $data;


			/* Hook preprocess gifbuilder conf
			 * Added by Julle for 3.8.0
			 *
			 * Let's you pre-process the gifbuilder configuration. for
			 * example you can split a string up into lines and render each
			 * line as TEXT obj, see extension julle_gifbconf
			 */

			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_gifbuilder.php']['gifbuilder-ConfPreProcess']))    {
				foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_gifbuilder.php']['gifbuilder-ConfPreProcess'] as $_funcRef)    {
					$_params = $this->setup;
					$this->setup = t3lib_div::callUserFunction($_funcRef,$_params,$this);
				}
			}

				// Initializing global Char Range Map
			$this->charRangeMap = array();
			if (is_array($GLOBALS['TSFE']->tmpl->setup['_GIFBUILDER.']['charRangeMap.']))	{
				foreach($GLOBALS['TSFE']->tmpl->setup['_GIFBUILDER.']['charRangeMap.'] as $cRMcfgkey => $cRMcfg)	{
					if (is_array($cRMcfg))	{

							// Initializing:
						$cRMkey = $GLOBALS['TSFE']->tmpl->setup['_GIFBUILDER.']['charRangeMap.'][substr($cRMcfgkey,0,-1)];
						$this->charRangeMap[$cRMkey] = array();
						$this->charRangeMap[$cRMkey]['charMapConfig'] =  $cRMcfg['charMapConfig.'];
						$this->charRangeMap[$cRMkey]['cfgKey'] = substr($cRMcfgkey,0,-1);
						$this->charRangeMap[$cRMkey]['multiplicator'] = (double)$cRMcfg['fontSizeMultiplicator'];
						$this->charRangeMap[$cRMkey]['pixelSpace'] = intval($cRMcfg['pixelSpaceFontSizeRef']);
					}
				}
			}

				// Getting sorted list of TypoScript keys from setup.
			$sKeyArray=t3lib_TStemplate::sortedKeyList($this->setup);

				// Setting the background color, passing it through stdWrap
			if ($conf['backColor.'] || $conf['backColor'])	{
				$cObj =t3lib_div::makeInstance('tslib_cObj');
				$cObj->start($this->data);
				$this->setup['backColor'] = trim($cObj->stdWrap($this->setup['backColor'], $this->setup['backColor.']));
			}
			if (!$this->setup['backColor'])	{ $this->setup['backColor']='white'; }

			if ($conf['transparentColor.'] || $conf['transparentColor'])	{
				$cObj =t3lib_div::makeInstance('tslib_cObj');
				$cObj->start($this->data);
				$this->setup['transparentColor_array'] = explode('|', trim($cObj->stdWrap($this->setup['transparentColor'], $this->setup['transparentColor.'])));
			}

 				// Transparency does not properly work when, GIFs or 8-bit PNGs are generated or reduceColors is set -- disable truecolor flag so they get generated "natively" in 8-bit.
 				// not working with reduceColors and truecolor images
 			if (($this->setup['transparentBackground'] || is_array($this->setup['transparentColor_array'])) && ($this->gifExtension=='gif' || !$this->png_truecolor || isset($this->setup['reduceColors'])))	{
 				$this->truecolor = false;
 			}

				// Set default dimensions
			if (!$this->setup['XY'])	{$this->setup['XY']='120,50';}


				// Checking TEXT and IMAGE objects for files. If any errors the objects are cleared.
				// The Bounding Box for the objects is stored in an array
			foreach($sKeyArray as $theKey) {
				$theValue = $this->setup[$theKey];

				if (intval($theKey) && $conf=$this->setup[$theKey.'.'])	{
						// Swipes through TEXT and IMAGE-objects
					switch($theValue)	{
						case 'TEXT':
							if ($this->setup[$theKey.'.'] = $this->checkTextObj($conf))	{

									// Adjust font width if max size is set:
								if ($this->setup[$theKey.'.']['maxWidth'])	{
									$this->setup[$theKey.'.']['fontSize'] = $this->fontResize($this->setup[$theKey.'.']); //RTF - this has to be done before calcBBox
								}

									// Calculate bounding box:
								$txtInfo=$this->calcBBox($this->setup[$theKey.'.']);
								$this->setup[$theKey.'.']['BBOX'] = $txtInfo;
								$this->objBB[$theKey] = $txtInfo;
								$this->setup[$theKey.'.']['imgMap'] = 0;
							}
						break;
						case 'IMAGE':
							$fileInfo = $this->getResource($conf['file'],$conf['file.']);
							if ($fileInfo)	{
								$this->combinedFileNames[] = ereg_replace('\.[[:alnum:]]+$','',basename($fileInfo[3]));
								$this->setup[$theKey.'.']['file'] = $fileInfo[3];
								$this->setup[$theKey.'.']['BBOX'] = $fileInfo;
								$this->objBB[$theKey] = $fileInfo;
								if ($conf['mask'])	{
									$maskInfo = $this->getResource($conf['mask'],$conf['mask.']);
									if ($maskInfo)	{
										$this->setup[$theKey.'.']['mask'] = $maskInfo[3];
									} else {
										$this->setup[$theKey.'.']['mask'] = '';
									}
								}
							} else {
								unset($this->setup[$theKey.'.']);
							}
						break;
					}
						// Checks if disabled is set... (this is also done in menu.php / imgmenu!!)
					if ($conf['if.'])	{
						$cObj =t3lib_div::makeInstance('tslib_cObj');
						$cObj->start($this->data);

						if (!$cObj->checkIf($conf['if.']))	{
							unset($this->setup[$theKey]);
							unset($this->setup[$theKey.'.']);
						}
					}
				}
			}

				// Calculate offsets on elements
			$this->setup['XY'] = $this->calcOffset($this->setup['XY']);
			$this->setup['offset'] = $this->calcOffset($this->setup['offset']);
			$this->setup['workArea'] = $this->calcOffset($this->setup['workArea']);

			foreach ($sKeyArray as $theKey) {
				$theValue=$this->setup[$theKey];

				if (intval($theKey) && $conf=$this->setup[$theKey.'.'])	{
					switch($theValue)	{
						case 'TEXT':
						case 'IMAGE':
							if ($this->setup[$theKey.'.']['offset'])	{
								$this->setup[$theKey.'.']['offset'] = $this->calcOffset($this->setup[$theKey.'.']['offset']);
							}
						break;
						case 'BOX':
							if ($this->setup[$theKey.'.']['dimensions'])	{
								$this->setup[$theKey.'.']['dimensions'] = $this->calcOffset($this->setup[$theKey.'.']['dimensions']);
							}
						break;
						case 'WORKAREA':
							if ($this->setup[$theKey.'.']['set'])	{
								$this->setup[$theKey.'.']['set'] = $this->calcOffset($this->setup[$theKey.'.']['set']);
							}
						break;
						case 'CROP':
							if ($this->setup[$theKey.'.']['crop'])	{
								$this->setup[$theKey.'.']['crop'] = $this->calcOffset($this->setup[$theKey.'.']['crop']);
							}
						break;
						case 'SCALE':
							if ($this->setup[$theKey.'.']['width'])	{
								$this->setup[$theKey.'.']['width'] = $this->calcOffset($this->setup[$theKey.'.']['width']);
							}
							if ($this->setup[$theKey.'.']['height'])	{
								$this->setup[$theKey.'.']['height'] = $this->calcOffset($this->setup[$theKey.'.']['height']);
							}
						break;
					}
				}
			}
				// Get trivial data
			$XY = t3lib_div::intExplode(',',$this->setup['XY']);
			$maxWidth = intval($this->setup['maxWidth']);
			$maxHeight = intval($this->setup['maxHeight']);

			$XY[0] = t3lib_div::intInRange($XY[0],1, $maxWidth?$maxWidth:2000);
			$XY[1] = t3lib_div::intInRange($XY[1],1, $maxHeight?$maxHeight:2000);
			$this->XY = $XY;
			$this->w = $XY[0];
			$this->h = $XY[1];
			$this->OFFSET = t3lib_div::intExplode(',',$this->setup['offset']);

			$this->setWorkArea($this->setup['workArea']);	// this sets the workArea
			$this->defaultWorkArea = $this->workArea;	// this sets the default to the current;
		}
	}

	/**
	 * Initiates the image file generation if ->setup is true and if the file did not exist already.
	 * Gets filename from fileName() and if file exists in typo3temp/ dir it will - of course - not be rendered again.
	 * Otherwise rendering means calling ->make(), then ->output(), then ->destroy()
	 *
	 * @return	string		The filename for the created GIF/PNG file. The filename will be prefixed "GB_"
	 * @see make(), fileName()
	 */
	function gifBuild()	{
		if ($this->setup)	{
			$gifFileName = $this->fileName('GB/');	// Relative to PATH_site
			if (!@file_exists($gifFileName))	{		// File exists

					// Create temporary directory if not done:
				$this->createTempSubDir('GB/');

					// Create file:
				$this->make();
				$this->output($gifFileName);
				$this->destroy();
			}
			return $gifFileName;
		}
	}

	/**
	 * The actual rendering of the image file.
	 * Basically sets the dimensions, the background color, the traverses the array of GIFBUILDER objects and finally setting the transparent color if defined.
	 * Creates a GDlib resource in $this->im and works on that
	 * Called by gifBuild()
	 *
	 * @return	void
	 * @access private
	 * @see gifBuild()
	 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=378&cHash=3c2ae4a1ab
	 */
	function make()	{
			// Get trivial data
		$XY = $this->XY;

			// Gif-start
		$this->im = $this->imagecreate($XY[0],$XY[1]);
		$this->w = $XY[0];
		$this->h = $XY[1];

			// backColor is set
		$BGcols = $this->convertColor($this->setup['backColor']);
		$Bcolor = ImageColorAllocate($this->im, $BGcols[0],$BGcols[1],$BGcols[2]);
		ImageFilledRectangle($this->im, 0, 0, $XY[0], $XY[1], $Bcolor);

			// Traverse the GIFBUILDER objects an render each one:
		if (is_array($this->setup))	{
			$sKeyArray=t3lib_TStemplate::sortedKeyList($this->setup);
			foreach($sKeyArray as $theKey)	{
				$theValue=$this->setup[$theKey];

				if (intval($theKey) && $conf=$this->setup[$theKey.'.'])	{
					switch($theValue)	{
							// Images
						case 'IMAGE':
							if ($conf['mask'])	{
								$this->maskImageOntoImage($this->im,$conf,$this->workArea);
							} else {
								$this->copyImageOntoImage($this->im,$conf,$this->workArea);
							}
						break;

							// Text
						case 'TEXT':
							if (!$conf['hide'])	{
								if (is_array($conf['shadow.']))	{
									$this->makeShadow($this->im,$conf['shadow.'],$this->workArea,$conf);
								}
								if (is_array($conf['emboss.']))	{
									$this->makeEmboss($this->im,$conf['emboss.'],$this->workArea,$conf);
								}
								if (is_array($conf['outline.']))	{
									$this->makeOutline($this->im,$conf['outline.'],$this->workArea,$conf);
								}
								$conf['imgMap']=1;
								$this->makeText($this->im,$conf,$this->workArea);
							}
						break;

							// Text effects:
						case 'OUTLINE':
							if ($this->setup[$conf['textObjNum']]=='TEXT' && $txtConf=$this->checkTextObj($this->setup[$conf['textObjNum'].'.']))	{
								$this->makeOutline($this->im,$conf,$this->workArea,$txtConf);
							}
						break;
						case 'EMBOSS':
							if ($this->setup[$conf['textObjNum']]=='TEXT' && $txtConf=$this->checkTextObj($this->setup[$conf['textObjNum'].'.']))	{
								$this->makeEmboss($this->im,$conf,$this->workArea,$txtConf);
							}
						break;
						case 'SHADOW':
							if ($this->setup[$conf['textObjNum']]=='TEXT' && $txtConf=$this->checkTextObj($this->setup[$conf['textObjNum'].'.']))	{
								$this->makeShadow($this->im,$conf,$this->workArea,$txtConf);
							}
						break;

							// Other
						case 'BOX':
							$this->makeBox($this->im,$conf,$this->workArea);
						break;
						case 'EFFECT':
							$this->makeEffect($this->im,$conf);
						break;
						case 'ADJUST':
							$this->adjust($this->im,$conf);
						break;
						case 'CROP':
							$this->crop($this->im,$conf);
						break;
						case 'SCALE':
							$this->scale($this->im,$conf);
						break;
						case 'WORKAREA':
							if ($conf['set'])	{
								$this->setWorkArea($conf['set']);	// this sets the workArea
							}
							if (isset($conf['clear']))	{
								$this->workArea = $this->defaultWorkArea;	// this sets the current to the default;
							}
						break;
					}
				}
			}
		}


		if ($this->setup['transparentBackground'])	{
				// Auto transparent background is set
			$Bcolor = ImageColorExact($this->im, $BGcols[0],$BGcols[1],$BGcols[2]);
			imagecolortransparent($this->im, $Bcolor);
		} elseif (is_array($this->setup['transparentColor_array']))	{
				// Multiple transparent colors are set. This is done via the trick that all transparent colors get converted to one color and then this one gets set as transparent as png/gif can just have one transparent color.
			$Tcolor = $this->unifyColors($this->im, $this->setup['transparentColor_array'], intval($this->setup['transparentColor.']['closest']));
			if ($Tcolor>=0)	{
				imagecolortransparent($this->im, $Tcolor);
			}
		}

	}


















	/*********************************************
	 *
	 * Various helper functions
	 *
	 ********************************************/


	/**
	 * Initializing/Cleaning of TypoScript properties for TEXT GIFBUILDER objects
	 *
	 * 'cleans' TEXT-object; Checks fontfile and other vital setup
	 * Finds the title if its a 'variable' (instantiates a cObj and loads it with the ->data record)
	 * Performs caseshift if any.
	 *
	 * @param	array		GIFBUILDER object TypoScript properties
	 * @return	array		Modified $conf array IF the "text" property is not blank
	 * @access private
	 */
	function checkTextObj($conf)	{
		$conf['fontFile']=$this->checkFile($conf['fontFile']);
		if (!$conf['fontFile']){$conf['fontFile']='t3lib/fonts/nimbus.ttf';}
		if (!$conf['iterations']){$conf['iterations'] = 1;}
		if (!$conf['fontSize']){$conf['fontSize']=12;}
		if ($conf['spacing'] || $conf['wordSpacing'])	{		// If any kind of spacing applys, we cannot use angles!!
			$conf['angle']=0;
		}
		if (!isset($conf['antiAlias'])){$conf['antiAlias']=1;}
		$cObj =t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($this->data);

		$conf['fontColor'] = trim($cObj->stdWrap($conf['fontColor'], $conf['fontColor.']));
		$conf['text']=$cObj->stdWrap($conf['text'],$conf['text.']);
			// Strip HTML
		if (!$conf['doNotStripHTML'])	{
			$conf['text'] = strip_tags($conf['text']);
		}
		$this->combinedTextStrings[] = strip_tags($conf['text']);

			// Max length = 100
		$tlen = intval($conf['textMaxLength']) ? intval($conf['textMaxLength']) : 100;
		$conf['text'] = substr($conf['text'],0,$tlen);
		if ((string)$conf['text']!='')	{

				// Char range map thingie:
			$fontBaseName = basename($conf['fontFile']);
			if (is_array($this->charRangeMap[$fontBaseName]))	{

					// Initialize splitRendering array:
				if (!is_array($conf['splitRendering.']))	{
					$conf['splitRendering.'] = array();
				}

				$cfgK = $this->charRangeMap[$fontBaseName]['cfgKey'];
				if (!isset($conf['splitRendering.'][$cfgK]))	{	// Do not impose settings if a splitRendering object already exists:
						// Set configuration:
					$conf['splitRendering.'][$cfgK] = 'charRange';
					$conf['splitRendering.'][$cfgK.'.'] = $this->charRangeMap[$fontBaseName]['charMapConfig'];

						// multiplicator of fontsize:
					if ($this->charRangeMap[$fontBaseName]['multiplicator'])	{
						$conf['splitRendering.'][$cfgK.'.']['fontSize'] = round($conf['fontSize'] * $this->charRangeMap[$fontBaseName]['multiplicator']);
					}
						// multiplicator of pixelSpace:
					if ($this->charRangeMap[$fontBaseName]['pixelSpace'])	{
						$travKeys = array('xSpaceBefore','xSpaceAfter','ySpaceBefore','ySpaceAfter');
						foreach($travKeys as $pxKey)	{
							if (isset($conf['splitRendering.'][$cfgK.'.'][$pxKey]))	{
								$conf['splitRendering.'][$cfgK.'.'][$pxKey] = round($conf['splitRendering.'][$cfgK.'.'][$pxKey] * ($conf['fontSize'] / $this->charRangeMap[$fontBaseName]['pixelSpace']));
							}
						}
					}
				}
			}
			if (is_array($conf['splitRendering.']))	{
				foreach($conf['splitRendering.'] as $key => $value)	{
					if (is_array($conf['splitRendering.'][$key]))	{
						if (isset($conf['splitRendering.'][$key]['fontFile']))	{
							$conf['splitRendering.'][$key]['fontFile'] = $this->checkFile($conf['splitRendering.'][$key]['fontFile']);
						}
					}
				}
			}

			return $conf;
		}
	}

	/**
	 * Calculation of offset using "splitCalc" and insertion of dimensions from other GIFBUILDER objects.
	 *
	 * Example:
	 * Input: 2+2, 2*3, 123, [10.w]
	 * Output: 4,6,123,45  (provided that the width of object in position 10 was 45 pixels wide)
	 *
	 * @param	string		The string to resolve/calculate the result of. The string is divided by a comma first and each resulting part is calculated into an integer.
	 * @return	string		The resolved string with each part (separated by comma) returned separated by comma
	 * @access private
	 */
	function calcOffset($string)	{
		$numbers=explode(',',$string);
		while(list($key,$val)=each($numbers))	{
			$val = trim($val);
			if ((string)$val==(string)intval($val)) {
				$value[$key]=intval($val);
			} else {
				$parts= t3lib_div::splitCalc($val,'+-*/%');
				$value[$key]=0;
				reset($parts);
				while(list(,$part)=each($parts))	{
					$theVal = $part[1];
					$sign =  $part[0];
					if ((string)intval($theVal)==(string)$theVal)	{
						$theVal = intval($theVal);
					} elseif ('['.substr($theVal,1,-1).']'==$theVal)	{
						$objParts=explode('.',substr($theVal,1,-1));
						$theVal=0;
						if (isset($this->objBB[$objParts[0]]))	{
							if ($objParts[1]=='w')	{$theVal=intval($this->objBB[$objParts[0]][0]);}
							if ($objParts[1]=='h')	{$theVal=intval($this->objBB[$objParts[0]][1]);}
						}
					} else {
						$theVal =0;
					}
					if ($sign=='-')	{$value[$key]-=$theVal;}
					if ($sign=='+')	{$value[$key]+=$theVal;}
					if ($sign=='/')	{if (intval($theVal)) $value[$key]/=intval($theVal);}
					if ($sign=='*')	{$value[$key]*=$theVal;}
					if ($sign=='%') {if (intval($theVal)) $value[$key]%=intval($theVal);}
				}
				$value[$key]=intval($value[$key]);
			}
		}
		$string = implode(',',$value);
		return $string;
	}

	/**
	 * Returns an "imgResource" creating an instance of the tslib_cObj class and calling tslib_cObj::getImgResource
	 *
	 * @param	string		Filename value OR the string "GIFBUILDER", see documentation in TSref for the "datatype" called "imgResource"
	 * @param	array		TypoScript properties passed to the function. Either GIFBUILDER properties or imgResource properties, depending on the value of $file (whether that is "GIFBUILDER" or a file reference)
	 * @return	array		Returns an array with file information if an image was returned. Otherwise false.
	 * @access private
	 * @see tslib_cObj::getImgResource()
	 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=315&cHash=63b593a934
	 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=282&cHash=831a95115d
	 */
	function getResource($file,$fileArray)	{
		if (!t3lib_div::inList($this->imageFileExt, $fileArray['ext']))	{
			$fileArray['ext'] = $this->gifExtension;
		}
		$cObj =t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($this->data);
		return $cObj->getImgResource($file,$fileArray);
	}

	/**
	 * Returns the reference to a "resource" in TypoScript.
	 *
	 * @param	string		The resource value.
	 * @return	string		Returns the relative filepath
	 * @access private
	 * @see t3lib_TStemplate::getFileName()
	 */
	function checkFile($file)	{
		return $GLOBALS['TSFE']->tmpl->getFileName($file);
	}

	/**
	 * Calculates the GIFBUILDER output filename/path based on a serialized, hashed value of this->setup
	 *
	 * @param	string		Filename prefix, eg. "GB_"
	 * @return	string		The relative filepath (relative to PATH_site)
	 * @access private
	 */
	function fileName($pre)	{

			// WARNING: In PHP5 I discovered that rendering with freetype of Japanese letters was totally corrupt. Not only the wrong glyphs are printed but also some memory stack overflow resulted in strange additional chars - and finally the reason for this investigation: The Bounding box data was changing all the time resulting in new images being generated all the time. With PHP4 it works fine.
		return $this->tempPath.
				$pre.
				($GLOBALS['TSFE']->config['config']['meaningfulTempFilePrefix'] ? $GLOBALS['TSFE']->fileNameASCIIPrefix(implode('_',array_merge($this->combinedTextStrings,$this->combinedFileNames)),intval($GLOBALS['TSFE']->config['config']['meaningfulTempFilePrefix']),'_') : '').
				t3lib_div::shortMD5(serialize($this->setup)).
				'.'.$this->extension();
	}

	/**
	 * Returns the file extension used in the filename
	 *
	 * @return	string		Extension; "jpg" or "gif"/"png"
	 * @access private
	 */
	function extension() {
		switch(strtolower($this->setup['format']))	{
			case 'jpg':
			case 'jpeg':
				return 'jpg';
			break;
			case 'png':
				return 'png';
			break;
			case 'gif':
				return 'gif';
			break;
			default:
				return $this->gifExtension;
			break;
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_gifbuilder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_gifbuilder.php']);
}

?>