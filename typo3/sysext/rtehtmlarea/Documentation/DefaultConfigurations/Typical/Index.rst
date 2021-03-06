﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt



.. _typical-configuration:

Typical default configuration
-----------------------------

This default configuration establishes default settings in Page
TSconfig and User TSconfig with most commonly used features
representing a good start for typical sites.


.. _typical-page-tsconfig:

The following is inserted in Page TSconfig:
"""""""""""""""""""""""""""""""""""""""""""

## Define labels and styles to be applied to class selectors in the
interface of the RTE

## The examples included here make partial re-use of color scheme and
frame scheme from CSS Styled Content extension

::

   RTE.classes {
           align-left {
                   name = LLL:EXT:rtehtmlarea/htmlarea/locallang_tooltips.xml:justifyleft
                   value = text-align: left;
           }
           align-center {
                   name = LLL:EXT:rtehtmlarea/htmlarea/locallang_tooltips.xml:justifycenter
                   value = text-align: center;
           }
           align-right {
                   name = LLL:EXT:rtehtmlarea/htmlarea/locallang_tooltips.xml:justifyright
                   value = text-align: right;
           }
           csc-frame-frame1 {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:frame-frame1
                   value = background-color: #EDEBF1; border: 1px solid #333333;
           }
           csc-frame-frame2 {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:frame-frame2
                   value = background-color: #F5FFAA; border: 1px solid #333333;
           }
           important {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:important
                   value = color: #8A0020;
           }
           name-of-person {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:name-of-person
                   value = color: #10007B;
           }
           detail {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:detail
                   value = color: #186900;
           }
           component-items {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:component-items
                   value = color: #186900;
           }
           action-items {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:action-items
                   value = color: #8A0020;
           }
           component-items-ordered {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:component-items
                   value = color: #186900;
           }
           action-items-ordered {
                   name = LLL:EXT:rtehtmlarea/res/contentcss/locallang.xml:action-items
                   value = color: #8A0020;
           }
   }

## Anchor classes configuration for use by the anchor accesibility
feature

::

   RTE.classesAnchor {
           externalLink {
                   class = external-link
                   type = url
                   titleText = LLL:EXT:rtehtmlarea/res/accessibilityicons/locallang.xml:external_link_titleText
           }
           externalLinkInNewWindow {
                   class = external-link-new-window
                   type = url
                   titleText = LLL:EXT:rtehtmlarea/res/accessibilityicons/locallang.xml:external_link_new_window_titleText
           }
           internalLink {
                   class = internal-link
                   type = page
                   titleText = LLL:EXT:rtehtmlarea/res/accessibilityicons/locallang.xml:internal_link_titleText
           }
           internalLinkInNewWindow {
                   class = internal-link-new-window
                   type = page
                   titleText = LLL:EXT:rtehtmlarea/res/accessibilityicons/locallang.xml:internal_link_new_window_titleText
           }
           download {
                   class = download
                   type = file
                   titleText = LLL:EXT:rtehtmlarea/res/accessibilityicons/locallang.xml:download_titleText
           }
           mail {
                   class = mail
                   type = mail
                   titleText = LLL:EXT:rtehtmlarea/res/accessibilityicons/locallang.xml:mail_titleText
           }
   }

## Default RTE configuration

::

   RTE.default {

## Markup options

::

      enableWordClean = 1
      removeTrailingBR = 1
      removeComments = 1
      removeTags = center, font, o:p, sdfield, strike, u
      removeTagsAndContents = link, meta, script, style, title

## Toolbar options

## The TCA configuration may add buttons to the toolbar

::

      showButtons (
                   blockstylelabel, blockstyle, textstylelabel, textstyle,
                   formatblock, bold, italic, subscript, superscript,
                   orderedlist, unorderedlist, outdent, indent, textindicator,
                   insertcharacter, link, table, findreplace, chMode, removeformat, undo, redo, about,
                   toggleborders, tableproperties,
                   rowproperties, rowinsertabove, rowinsertunder, rowdelete, rowsplit,
                   columninsertbefore, columninsertafter, columndelete, columnsplit,
                   cellproperties, cellinsertbefore, cellinsertafter, celldelete, cellsplit, cellmerge
      )

## More toolbar options

::

      keepButtonGroupTogether = 1

## Enable status bar

::

      showStatusBar =  1

## Hide infrequently used block types in the block formatting selector

::

      buttons.formatblock.removeItems = pre,address

## List all class selectors that are allowed on the way to the
database

::

      proc.allowedClasses (
              external-link, external-link-new-window, internal-link, internal-link-new-window, download, mail,
              align-left, align-center, align-right, align-justify,
              csc-frame-frame1, csc-frame-frame2,
              component-items, action-items,
              component-items-ordered, action-items-ordered,
              important, name-of-person, detail,
              indent
      )

## Restrict the list of class selectors presented by the RTE to the
following for the specified tags:

::

      buttons.blockstyle.tags.div.allowedClasses (
              align-left, align-center, align-right,
              csc-frame-frame1, csc-frame-frame2
      )
      buttons.blockstyle.tags.table.allowedClasses = csc-frame-frame1, csc-frame-frame2
      buttons.blockstyle.tags.td.allowedClasses = align-left, align-center, align-right
      buttons.textstyle.tags.span.allowedClasses = important, name-of-person, detail

## Configuration of classes for links

## These classes should also be in the list proc.allowedClasses

::

      buttons.link.properties.class.allowedClasses = external-link, external-link-new-window, internal-link, internal-link-new-window, download, mail
      buttons.link.page.properties.class.default = internal-link
      buttons.link.url.properties.class.default = external-link-new-window
      buttons.link.file.properties.class.default = download
      buttons.link.mail.properties.class.default = mail

## Configuration specific to the TableOperations feature

## Remove the following fieldsets from the properties popups

::

      disableAlignmentFieldsetInTableOperations = 1
      disableSpacingFieldsetInTableOperations = 1
      disableColorFieldsetInTableOperations = 1
      disableLayoutFieldsetInTableOperations = 1

## Show borders on table creation

::

      buttons.toggleborders.setOnTableCreation = 1

## Configuration specific to the bold and italic buttons

## Add hotkeys associated with bold and italic buttons

::

      buttons.bold.hotKey = b
      buttons.italic.hotKey = i

## Configuration of microdata schema

::

      schema {
                   sources {
                           schemaOrg = EXT:rtehtmlarea/extensions/MicrodataSchema/res/schemaOrgAll.rdf
                   }
           }
   }

## front end RTE configuration for the general public

::

   RTE.default.FE < RTE.default
   RTE.default.FE.showStatusBar = 0
   RTE.default.FE.hideButtons = chMode, blockstyle, textstyle, underline, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
   RTE.default.FE.userElements >
   RTE.default.FE.userLinks >

## tt\_content TCEFORM configuration

## Let use all the space available for more comfort.

::

   TCEFORM.tt_content.bodytext.RTEfullScreenWidth = 100%


.. _typical-user-tsconfig:

The following is inserted in User TSconfig:
"""""""""""""""""""""""""""""""""""""""""""

## Enable the RTE by default for all users

::

   setup.default.edit_RTE = 1



