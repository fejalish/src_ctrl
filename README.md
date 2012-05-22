##Overview

{src_ctrl} is a server side image resizer for use with responsive websites.

It uses a combination of Javascript, server side scripting (PHP with imagick) and .htaccess rewrites to serve up custom resized images to fit a web page's responsive layout.

The service works by comparing the client's screen width against the range of percentage widths that an image's containing element is set at through the CSS's various media-query break-points.

## How to Use

Here is an example img tag:

```
<img src="http://src.yourdomain.com/{min:320,max:479,per:1.0,quality:75|min:480,max:767,per:0.4512,quality:75|min:768,max:*,per:0.2208,quality:75}/http://yourdomain.com/images/filename.jpg" />
```

The image's specific media-query ranges are set after the resizing service domain, and location of the image is set after the ranges. This is all run through the resizing service which grabs the image, resizes and serves it back up to the client.

The src can contain any number of ranges, but each range must contain a minimum ("min") and maximum ("max") screen width and the percentage ("per") to which an image needs to be resized. The compression "quality" setting is optional.

The above example covers three width ranges:

- 320 to 479px
- 480 to 767px
- 768 to *

The percentage widths are calculated by combining the percentages of all of the image's parent elements, from the immediate parent all the way back to the body.

## Caveats

There's no perfect solution for serving up images for responsive websites. This method, of course, has it's caveats.

- This method requires Javascript to send the browser's clientWidth value to the server.
- Without the clientWidth data being sent or stored in the server session variable (first virgin load or no-Javascript browser) all images are resized to a mobile-friendly baseline width in the code.
- The script returns images through a "Header: location" redirect. So there is the initial HTTP request for the "src" call and then a second request for the final resized image. A redirect and two HTTP requests per image is not great, but it does allow for the final individual images to be be cached through a CDN.
- One must know _all_ the possible percentage sizes of an image's containing element in all media-query ranges to properly use this method. (This of course takes some calculation - which can be tedious - but it is not difficult.)

## Features

- There is a baseline screen width for processing images without knowing the browser's clientWidth value. Currently the default it is set to 640px to minimise on file-size for low-bandwidth/narrow-screen devices but also to retain enough quality when images are embiggened on wider-screen devices (monitors, TVs, etc). This can be adjusted in the settings.
- The script resizes images rounded up to the nearest 20 pixel value, so as to save on processing load. This can be adjusted in the settings.
- It works with CSS background images. Yay! (Have to test CSS sprites still, but should work as well).
- There's also a basic flush call to clear the original image from the resize server as well as all the resized images. Here is a sample URL: http://src.fejalish.com/{f}/http://src.yourdomain.com/images/filename.jpg

## Notes

- Scrollbars reduce the clientWidth of the screen, so when scollbars are present images may be a bit over the 20px range allowed for in the code.
- Image resizing can be processor intensive of course, especially when a page has many, many images which have not been processed previously. Make sure your server can handle your specific processing load to avoid unrendered images.
- It is best not to use large, uncompressed images. Instead save large, lightly compressed images with reasonable compression before using on a live site. If the baseline variable is not set and the clientWidth cannot be read the original image is set as the default download, which of course kills low-bandwidth connections and increases page-weight dramatically.

## Demo

A demo will be coming soon.