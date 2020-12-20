Image Processor
===============

.. contents::
    :local:

Description
-----------

Pluggable, extensible module to crop, resize, and scale images in Magento.

Installation
------------

.. code-block:: sh

    composer require auroraextensions/imageprocessor

Usage
-----

.. code-block:: php

   ...

   /** @var string $thumbnail */
   $thumbnail = $this->imageProcessor->resize($imagePath);

   ...
