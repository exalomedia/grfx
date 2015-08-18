<?php
/**
 * grfx redirects.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * @package   grfx_Admin
 * @author     Leo Blanchette <leo@grfx.co>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       https://www.facebook.com/grfx.co
 */

/**
 * Redirects to setting page after plugin is activated
 * @param type $plugin
 */
function grfx_activation_redirect( $plugin ) {
    if( $plugin == grfx_plugin_basename() ) {
        exit( wp_redirect( admin_url( 'options-general.php?page=grfx' ) ) );
    }
}
add_action( 'activated_plugin', 'grfx_activation_redirect' );