##Overview

{src_ctrl} is a server side image resizer for use with responsive websites.

It uses a combination of Javascript, server side scripting (PHP) and .htaccess rewrites to serve up custom resized images to fit a web page's responsive layout.

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

- One must know _all_ the possible percentage sizes of an image's containing element in all media-query ranges. (This of course takes some calculation - which can be tedious - but it is not difficult.)
- This method requires Javascript to send the browser's screen width to the server.
- The client's screen width is stored in the server session so the first virgin load will source all images at whichever resolution is determined by the code - full resolution, mobile-first reduced images, etc. (Currently the script returns full resolution images.)
- The script returns images through a "Header: location" redirect. So there is the initial HTTP request for the "src" call and then a second request for the final resized image. It does add multiple requests, which is not great, but it does allow for the individual images to be be cached through a CDN.
- The script resizes images rounded up to the nearest 20 pixel modulo, so as to save on processing load.(This can be changed in the configuration variables.)

## Demo

A demo will be coming soon.