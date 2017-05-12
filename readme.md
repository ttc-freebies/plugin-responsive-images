# Responsive images for articles

## What is it?
A tiny content plugin that create a few different sized images on the fly and also update the markup to use the well established and supported HTML5 image tag srcset

## What type of images are supported?
Only jpg and png

## Does it also take care of the intro and full image of an article?
No.

## Are there any precautions?
YES! Because the plugin creates the images on the fly if a user saves a new article then the first time a visitor visits that page the load time can be very long. An easy work around is that the creator saves the article with some elevated permissions and then visits the page (granted they have permissions) and then re-edit the article to restore the preferred permissions.
Also if you already have a live site, it's highly recommended to create the extra images locally (eg use some browser plugin to visit all your pages in your sitemap).
The extra images are placed in `/media/cached-resp-images`

## Installation
Just upload the zip!
Then enable the plugin and you're set. Nothing to configure!

## Is there a way to clean the cached images?
Sure: just ftp, ssh or use your host control panel and remove the folder named `cached-res-images` inside the folder `media`.
CAUTION: Performing this action will force the plugin to regenerate all the required image sizes for each image which will effectively increase geometrically the loading time for each page delivered for the first time!!!

## Documentation
Read the above, there is nothing more to document here [there are no options other than enable/disable, so...].

## Support
The only valid support will be fixing possible bugs...

## Are there any plans for more features?
Not for this free version.
