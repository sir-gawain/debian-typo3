<?php
/*
 * @deprecated since 6.0, the classname tslib_gmenu_foldout and this file is obsolete
 * and will be removed by 7.0. The class was renamed and is now located at:
 * typo3/sysext/frontend/Classes/ContentObject/Menu/GraphicalMenuFoldout.php
 */
require_once t3lib_extMgm::extPath('frontend') . 'Classes/ContentObject/Menu/GraphicalMenuFoldout.php';
$GLOBALS['TSFE']->tmpl->menuclasses .= ',gmenu_foldout';
?>