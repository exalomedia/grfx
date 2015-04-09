# grfx (graphics) - the Illustrator's Stock Image Authoring Tool

 - **grfx** is a specialized open source tool for illustrators and graphic artists to independently publish stock images.
 - **grfx** empowers you to sell the following: **Images, vectors, photoshop files, zip files, and more.**
 - **grfx** supports multiple licenses to sell your files under.
 - **grfx** is a wordpress plugin.
 - **grfx** is a woocommerce extension. Get Woocommerce here: https://wordpress.org/plugins/woocommerce/

**grfx** can be used with any woocommerce compatible wordpress theme. Get one of those beautiful themes on http://www.themeforest.net and really wow your customers with your beautiful site and stock images for sale!

Are you an illustrator or graphic artist? Have you ever heard of microstock? See more here: http://en.wikipedia.org/wiki/Microstock_photography

# Installation Prerequisites 
Since **grfx** is a wordpress plugin, dependent on woocommerce, do this first:
 - Create a wordpress site. https://codex.wordpress.org/New_To_WordPress_-_Where_to_Start
 - Install Woocommerce https://wordpress.org/plugins/woocommerce/installation/

# Installing the grfx Authoring Tool
Its important to make the initial effort to get your site set up *just right*! Be sure to go over these installation instructions so that you are selling images at top quality.

## 1: First, ensure ImageMagick and Imagick is working.
Most servers have imagemagick installed. ImageMagick is a software suite to create, edit, compose, or convert images. ( http://www.imagemagick.org/ ) It is absolutely imperative that imagemagick is installed or the system will default to php's GD-Library (not good!). ImageMagick preserves color quality and produces beautiful results, ensuring your customer receives the best quality resized image.

Most installations have imagemagick installed. You need only activate Imagick (php's ImageMagick wrapper http://php.net/manual/en/class.imagick.php ). This can be done via the following:

 - Go to your root directory (where Wordpress was installed)
 - Open up the ```php.ini```file (if it is not there, create it).
 - Insert the following at the very end of that file: ```extension=imagick.so```
 - Save the ```php.ini``` file.
 
Need help? Most hosts can set this up for you if you call them.

## 2: Modify ```wp-config.php```

 - Go to your root directory (where Wordpress was installed)
 - Open up the file ```wp-config.php```.
 - Find the line that says: ```/* That's all, stop editing! Happy blogging. */```:
 - Just ABOVE that line, paste this code: ```if(defined('GRFX_GETTING_INFO')) return; ```

## 3: Activate!
Now, go to your wordpress plugins directory [admin->plugins] and activate the **grfx** plugin. Things are getting really awesome now.

## 4: Set up your store info.

- Once activating, you be taken to an introduction page with three links.
- Follow each link, and fully configure your price and license settings. It should be self-explanatory.
- Now configure woocommerce itself for all of your basic store and payment gateway information.

## Want to run a grfx network?
**grfx**'s huge advantage is that it is multisite compatible! This means you can host a whole bunch of artists! If you can host a multisite network, then you must be really smart with wordpress! Please contribute to our code and help our growth.

Curious what the heck we are talking about? http://codex.wordpress.org/Create_A_Network

#DEVELOPERS

**grfx** is layed out and commented as well as possible to help you expand and improve our system. You are very essential to our develpment. Please join the github project and make contributions, file bug reports, and do developer stuff! 

**Want to see the code docs?** They are here: http://grfx.co/docs/
