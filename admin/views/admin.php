<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   grfx
 * @author    Leo Blanchette <clipartillustration.com@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.grfx.com
 * @copyright 2014 Leo Blanchette
 */
?>

<div class="wrap">

	
	<?php echo grfx_logo_img(2) ?>

	<hr />
	<h4 class="description"><?php _e("Stock imaging for illustrators.", "grfx") ?></h4>
	
	<ol>
		
		<!-- STEP 1 -->
		<li>
			<strong><?php _e("First, ", "grfx") ?></strong>
				<a target="_blank" title="<?php _e("grfx Settings", "grfx") ?>" href="<?php echo admin_url('admin.php?page=wc-settings&tab=settings_grfx') ?>">
					<?php _e("Configure your main settings...", "grfx") ?>
				</a>		
		</li>
		
		<!-- STEP 2 -->		
		<li>
			<strong><?php _e("Next, ", "grfx") ?></strong>
				<a target="_blank" title="<?php _e("grfx Defaults", "grfx") ?>" href="<?php echo admin_url('admin.php?page=wc-settings&tab=products&section=grfx_stock_image') ?>">
					<?php _e("Set your image upload defaults...", "grfx") ?>
				</a>	
		</li>
		
		<!-- STEP 3 -->		
		<li>
			<strong><?php _e("Then, ", "grfx") ?></strong>
				<a target="_blank" title="<?php _e("grfx Upload", "grfx") ?>" href="<?php echo admin_url('edit.php?post_type=product&page=grfx_uploader') ?>">
					<?php _e("Start uploading!", "grfx") ?>
				</a><br />	<br />	
		</li>		
		
        
        <!-- STEP 4 -->
        
        <li>            
            <p>
            <?php
            _e('Need help? Join the community at <strong><a href="http://community.grfx.co/">community.grfx.co/</a></strong>. We want your image-selling
            start-up to be successful. We can help you get up and running plus give you pointers to getting your work exposure.', 'grfx');
            ?>
            </p>
        </li>
        
	</ol>
	    
</div>

