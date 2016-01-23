=== Wordpress Stock Images by grfx ===
Contributors: grfx
Tags: wooCommerce stock images, stock photos, image library, stock photography, shop, watermark, image seo, photo gallery, image, sell photos, graphic design, album, stock images, photo, pictures, gallery, sell stockphotos, online art, photographer, multisite, image-store, clipart, image gallery, picture, sell, online store, album, digital images, vectors, royalty free, illustrations, graphics, media, store, ecommerce, photoblog, photos, seo, graphicart, artstore, digital downloads, illustrator, stockphotos, photogallery, shopping, photography store, sell photographs, e-commerce, royalty free images, watermarking, sell pictures, portfolio, thumbnails, macrostock, grfx, photography, image hosting, artist, photo management, digital art, woocommerce, art, selling, image album, image processing
Requires at least: 4.2.0
Tested up to: 4.4
Stable tag: 1.1.68
License: gpl
License URI: http://www.gnu.org/licenses/#GPL

Extends Woocommerce to allow you to mass-upload and sell stock images. Sell your images professionally and easily.


== Description ==
Extends woocommerce to allow you to mass-upload and sell stock images. Sell multiple image sizes, licenses, file types, useful for any image market but optimized for illustrators. From **grfx**, the illustrator network ( https://www.facebook.com/grfx.co )

Reads meta-data from images, streamlining your upload/description process. Huge productivity advantage - no tedious editing.

Good community support, **strong SEO**. Created for multisite and personal sites.

- **Run a network, or go solo:** Run your own bustling image selling network or sell off of your own site. Utilize WPMU to run an agency/network via grfx (https://premium.wpmudev.org/)
- **Compatible with Woocommerce and Woocommerce themes:** Enjoy the benefits, security, and extensibility of a Woocommerce based store that sells stock images.
- **FTP Support:** Bypass the uploader and easily FTP your images.
- **Automatically watermarks your images:** Protect your images with a professional looking watermark - automatically provided or of your creation.
- **Automated image processing:** - Set it and forget it. Process thousands of images at once.
- **Set your own licenses:** Easily set End User License Agreements (EULA) for your images.
- **Processes image Metadata:** Extracts title, keywords, and description from your image files and applies them to your products, saving you time.
- **Tested and stable**: We've been doing this a long time, and this package is well-tested and stable.

**More details...**

Available on Github: https://github.com/orangeman555/grfx

grfx (graphics) - the Illustrator's Stock Image Authoring Tool ( https://www.facebook.com/grfx.co/ )

- **grfx** is a specialized open source tool for illustrators and graphic artists to independently publish stock images.
- **grfx** empowers you to sell the following: **Images, vectors, photoshop files, zip files, and more.**
- **grfx** supports multiple licenses to sell your files under.
- **grfx** is a woocommerce extension. Get Woocommerce here: https://wordpress.org/plugins/woocommerce/
- **grfx** will automatically take your image uploads and process them straight into ready-made woocommerce stock image products -- it's just like running your own personal stock image agency!
- **grfx** is the first open source software spearheading the "indie-stock" movement. https://www.facebook.com/grfx.co/the-grfx-invention-of-indie-stock/
- **grfx** can be used with any woocommerce compatible wordpress theme. Get one of those beautiful woocommerce-based themes and really wow your customers with your beautiful site and stock images for sale!

Are you an illustrator or graphic artist? Have you ever heard of stock imaging? See more here: http://en.wikipedia.org/wiki/Stock_image

**DEVELOPERS**

**grfx** is layed out and commented as well as possible to help you expand and improve our system. You are very essential to our develpment. Please join the github project and make contributions, file bug reports, and do developer stuff! 

**Want to see the code docs?** They are here: http://grfx.co/docs/

To ensure a proper environment, we recommend [Bluehost](http://www.bluehost.com/track/grfx/ "Bluehost") or equivelent, since they allow all special functionality required for this plugin. 


== Installation ==

1. Download your WordPress Plugin to your desktop.
2. If downloaded as a zip archive, extract the Plugin folder to your desktop.
3. Read through the \"readme\" file thoroughly to ensure you follow the installation instructions.
4. With your FTP program, upload the Plugin folder to the wp-content/plugins folder in your WordPress directory online.
5. Go to Plugins screen and find the newly uploaded Plugin in the list.
6. Click Activate Plugin to activate it.

------------
This isn't just some image plugin - it turns your site into a professional, high quality stock image selling store.
Be sure to follow these installation directions for best results.

To ensure a proper environment, we recommend [Bluehost](http://www.bluehost.com/track/grfx/ "Bluehost") or equivelent, since they allow all special functionality required for this plugin. 

**Installing the grfx Authoring Tool**

Its important to make the initial effort to get your site set up *just right*! Be sure to go over these installation instructions so that you are selling images at top quality.

**1: First, ensure ImageMagick and Imagick is working.**

Most servers have imagemagick installed. ImageMagick is a software suite to create, edit, compose, or convert images. ( http://www.imagemagick.org/ ) It is absolutely imperative that imagemagick is installed or the system will default to php's GD-Library (not good!). ImageMagick preserves color quality and produces beautiful results, ensuring your customer receives the best quality resized image.

Most installations have imagemagick installed. You need only activate Imagick (php's ImageMagick wrapper http://php.net/manual/en/class.imagick.php ). This can be done via the following:

- Go to your root directory (where Wordpress was installed)
- Open up the ```php.ini```file (if it is not there, create it).
- Insert the following at the very end of that file: ```extension=imagick.so```
- Save the ```php.ini``` file.
 
Need help? Most hosts can set this up for you if you call them.

**2: Modify ```wp-config.php```**

- Go to your root directory (where Wordpress was installed)
- Open up the file ```wp-config.php```.
- Find the line that says: ```/* That's all, stop editing! Happy blogging. */```:
- Just ABOVE that line, paste this code: ```if(defined('GRFX_GETTING_INFO')) return; ```

**3: Activate!**

- Install the grfx.zip file the way you install any wordpress plugin. 
- NOTE: If you downloaded the zip from github, rename it to grfx.zip. If you have the unzipped folder (such as a mac user) rezip it and ensure that it is called grfx.zip. 
- Don't know how to install a plugin? https://codex.wordpress.org/Managing_Plugins
- Now, go to your wordpress plugins directory [admin->plugins] and activate the **grfx** plugin. Things are getting really awesome now.

**4: Set up your store info.**

- Once activating, you be taken to an introduction page with three links.
- Follow each link, and fully configure your price and license settings. It should be self-explanatory.
- Now configure woocommerce itself for all of your basic store and payment gateway information.

**5: Setting up the Cron (optional?)**

This is optional for single site, **required for multisite**. It is suggested you set up this feature if you upload a lot. Once you upload your images and enqueue them, your site will publish them a few at a time until they are done. What is the advantage in this? If your one of those super-dedicated stock image creators, you can upload a thousand of your meta-data-prepared images, go to bed, and wake up with them all published.

For a **multisite** network its, no joke, highly recommended you get this feature active on your site. If not, several users publishing at once could cause overloads. Otherwise multiple user's files will be processed in line, one after another. 

**Setting up Cron - Simple**

Chances are, if you are on a popular host such as [Bluehost](http://www.bluehost.com/track/grfx/ "Bluehost") with cpanel, cron jobs are easy to set up. 

- Copy the cron-command from your **Woocommerce->Settings->grfx** area. It should look like this: ```* * * * * curl --silent 'http://www.mysite.com/grfx/test/wp-content/plugins/grfx/cron.php?grfx_crontype=1&grfx_cronpass=8eb09aa5edbf60ef499c682a90916c28'```
- Delete the leading five asterisks, so that it looks like this: ```curl --silent 'http://www.mysite.com/grfx/test/wp-content/plugins/grfx/cron.php?grfx_crontype=1&grfx_cronpass=8eb09aa5edbf60ef499c682a90916c28'```
- Go to your Cron Jobs area, and set up a cron job for **every minute**. 
- Paste the above code into the command area. 
- Save.

...the cron job should run every minute now. When you upload images, it will start within a minute of their being on your server.

**Setting up Cron - Less Simple**

Well, really, this is simple if your used to server-side stuff and the shell. 

- Get the cron command from your settings area (as stated above). It should look like this: ```* * * * * curl --silent 'http://www.mysite.com/grfx/test/wp-content/plugins/grfx/cron.php?grfx_crontype=1&grfx_cronpass=8eb09aa5edbf60ef499c682a90916c28'```
- Log into your server with the shell. 
- type: ``` crontab -e``` (or similar for your environment)
- Paste the above line into your cron file.
- Save it.

**Cron Note:**
The cron job merely runs a check it runs every minute. If no images are staged, no server resources are used. If it finds images, it processes them a few at a time.

**Want to run a grfx network?**
**grfx**'s huge advantage is that it is **multisite compatible!** This means you can host a whole bunch of artists! If you can host a multisite network, then you must be really smart with wordpress! Please contribute to our code and help our growth.

Curious what the heck we are talking about? http://codex.wordpress.org/Create_A_Network


== Frequently Asked Questions ==
We will provide a frequently asked questions area when people start frequently asking questions. :D

== Screenshots ==
1. High quality, SEO'd image previews. Non-invasive, simple interface.
2. Preview images automatically watermarked for copyright protection -- you can assign your own watermark.
3. Specialized uploader that processes images directly into products. Automatically extracts titles, description, and keywords from image metadata. Upload and process hundreds of images, ready to sell, within minutes.
4. Simple license assignment. Create your own independent stock image license. 
5. Embeds into the plugin Woocommerce as an extension. Does not clutter your admin area.

== Changelog ==
First Commit - ready to make Wordpress.org just a little happier.

== Upgrade Notice ==
No upgrades yet.
