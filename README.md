# grfx (graphics) - the Illustrator's Stock Image Authoring Tool

 - **grfx** is a specialized open source tool for illustrators and graphic artists to independently publish stock images.
 - **grfx** empowers you to sell the following: **Images, vectors, photoshop files, zip files, and more.**
 - **grfx** supports multiple licenses to sell your files under.
 - **grfx** is a wordpress plugin.
 - **grfx** is a woocommerce extension. Get Woocommerce here: https://wordpress.org/plugins/woocommerce/
 - **grfx** will automatically take your image uploads and process them straight into ready-made woocommerce stock image products -- it's just like running your own personal stock image agency!

**grfx** can be used with any woocommerce compatible wordpress theme. Get one of those beautiful themes on http://www.themeforest.net and really wow your customers with your beautiful site and stock images for sale!

Are you an illustrator or graphic artist? Have you ever heard of microstock? See more here: http://en.wikipedia.org/wiki/Microstock_photography

# Installation Prerequisites 
Since **grfx** is a wordpress plugin, dependent on woocommerce, do this first:
 - Create a wordpress site. https://codex.wordpress.org/New_To_WordPress_-_Where_to_Start
 - Install Woocommerce https://wordpress.org/plugins/woocommerce/installation/

## Installing the grfx Authoring Tool
Its important to make the initial effort to get your site set up *just right*! Be sure to go over these installation instructions so that you are selling images at top quality.

### 1: First, ensure ImageMagick and Imagick is working.
Most servers have imagemagick installed. ImageMagick is a software suite to create, edit, compose, or convert images. ( http://www.imagemagick.org/ ) It is absolutely imperative that imagemagick is installed or the system will default to php's GD-Library (not good!). ImageMagick preserves color quality and produces beautiful results, ensuring your customer receives the best quality resized image.

Most installations have imagemagick installed. You need only activate Imagick (php's ImageMagick wrapper http://php.net/manual/en/class.imagick.php ). This can be done via the following:

 - Go to your root directory (where Wordpress was installed)
 - Open up the ```php.ini```file (if it is not there, create it).
 - Insert the following at the very end of that file: ```extension=imagick.so```
 - Save the ```php.ini``` file.
 
Need help? Most hosts can set this up for you if you call them.

### 2: Modify ```wp-config.php```

 - Go to your root directory (where Wordpress was installed)
 - Open up the file ```wp-config.php```.
 - Find the line that says: ```/* That's all, stop editing! Happy blogging. */```:
 - Just ABOVE that line, paste this code: ```if(defined('GRFX_GETTING_INFO')) return; ```

### 3: Activate!

- Install the grfx.zip file the way you install any wordpress plugin. 
- NOTE: If you downloaded the zip from github, rename it to grfx.zip. If you have the unzipped folder (such as a mac user) rezip it and ensure that it is called grfx.zip. 
- Don't know how to install a plugin? https://codex.wordpress.org/Managing_Plugins
- Now, go to your wordpress plugins directory [admin->plugins] and activate the **grfx** plugin. Things are getting really awesome now.

### 4: Set up your store info.

- Once activating, you be taken to an introduction page with three links.
- Follow each link, and fully configure your price and license settings. It should be self-explanatory.
- Now configure woocommerce itself for all of your basic store and payment gateway information.

### 5: Setting up the Cron (optional?)
This is optional for single site, **required for multisite**. It is suggested you set up this feature if you upload a lot. Once you upload your images and enqueue them, your site will publish them a few at a time until they are done. What is the advantage in this? If your one of those super-dedicated stock image creators, you can upload a thousand of your meta-data-prepared images, go to bed, and wake up with them all published.

For a **multisite** network its, no joke, highly recommended you get this feature active on your site. If not, several users publishing at once could cause overloads. Otherwise multiple user's files will be processed in line, one after another. 

#### Setting up Cron - Simple
Chances are, if you are on a popular host such as BLUEHOST with cpanel, cron jobs are easy to set up. 
- Copy the cron-command from your **Woocommerce->Settings->grfx** area. It should look like this:
```* * * * * curl --silent 'http://www.mysite.com/grfx/test/wp-content/plugins/grfx/cron.php?grfx_crontype=1&grfx_cronpass=8eb09aa5edbf60ef499c682a90916c28'```
- Delete the leading five asterisks, so that it looks like this: 
```curl --silent 'http://www.mysite.com/grfx/test/wp-content/plugins/grfx/cron.php?grfx_crontype=1&grfx_cronpass=8eb09aa5edbf60ef499c682a90916c28'```
- Go to your Cron Jobs area, and set up a cron job for **every minute**. 
- Paste the above code into the command area. 
- Save.

...the cron job should run every minute now. When you upload images, it will start within a minute of their being on your server.

#### Setting up Cron - Less Simple
Well, really, this is simple if your used to server-side stuff and the shell. 
- Get the cron command from your settings area (as stated above). It should look like this: ```* * * * * curl --silent 'http://www.mysite.com/grfx/test/wp-content/plugins/grfx/cron.php?grfx_crontype=1&grfx_cronpass=8eb09aa5edbf60ef499c682a90916c28'```
- Log into your server with the shell. 
- type: ``` crontab -e``` (or similar for your environment)
- Paste the above line into your cron file.
- Save it.

#### Cron Note:
The cron job merely runs a check it runs every minute. If no images are staged, no server resources are used. If it finds images, it processes them a few at a time.

## Want to run a grfx network?
**grfx**'s huge advantage is that it is multisite compatible! This means you can host a whole bunch of artists! If you can host a multisite network, then you must be really smart with wordpress! Please contribute to our code and help our growth.

Curious what the heck we are talking about? http://codex.wordpress.org/Create_A_Network

#DEVELOPERS

**grfx** is layed out and commented as well as possible to help you expand and improve our system. You are very essential to our develpment. Please join the github project and make contributions, file bug reports, and do developer stuff! 

**Want to see the code docs?** They are here: http://grfx.co/docs/
