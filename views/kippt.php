<?php
/*
Plugin Name: Kippt widget for Wordpress
Plugin URI: http://helsinkipromo.com
Description: Displays all public lists or most recent items added to Kippt.
Author: Kenneth Blomqvist
Version: 0.15
Author URI: http://twitter.com/kekeblom
License:

  Copyright 2012 (kenneth.blomqvist@helsinkipromo.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class KipptWidget extends WP_Widget
{
	/**
	 * The widget constructor. Specifies the classname and description, instantiates
	 * the widget, loads localization files, and includes necessary scripts and
	 * styles.
	 */
	public function __construct() {
	
		//load_plugin_textdomain( 'KipptWidget-locale', false, plugin_dir_path( __FILE__ ) . '/lang/' );
		
		// register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		// register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		
		parent::__construct(
			'KipptWidget-id',
			'KipptWidget',
			array(
				'classname'		=>	'KipptWidget-class',
				'description'	=>	__( 'Displays all public kippts', 'KipptWidget-locale' )
			)
		);
	
		
		
	} // end constructor


	function form($instance)
	{
		$instance = wp_parse_args( (array) $instance, array( 
			'title' => 'What we read',
			'username' => '',
			'token' => '',
			'mode' => '1',
			'credit' => '0' ) );
		$title = $instance['title'];
		$username = $instance['username'];
		$token = $instance['token'];
		$mode = $instance['mode'];
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p> 
	<p><label for="<?php echo $this->get_field_id('username'); ?>">Username: <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo esc_attr($username); ?>" /></label></p> 
	<p><label for="<?php echo $this->get_field_id('token'); ?>">Api Token: <input class="widefat" id="<?php echo $this->get_field_id('token'); ?>" name="<?php echo $this->get_field_name('token'); ?>" type="text" value="<?php echo esc_attr($token); ?>" /></label></p> 
	<p>Widget behaviour:<br />
		<label for="<?php echo $this->get_field_id('mode'); ?>">
			<input name="<?php echo $this->get_field_name('mode'); ?>" type="radio" value="0" <?php checked( '0', $instance['mode'] ); ?> /> Shows latest clips <br />
		</label>
		<label for="<?php echo $this->get_field_id('mode'); ?>">
			<input name="<?php echo $this->get_field_name('mode'); ?>" type="radio" value="1" <?php checked( '1', $instance['mode'] ); ?> /> Shows all public lists <br />
		</label>
	<p>If you like the widget, consider linking back to our site:<br />
		<label for="<?php echo $this->get_field_id('credit'); ?>">
			<input name="<?php echo $this->get_field_name('credit'); ?>" type="radio" value="0" <?php checked( '0', $instance['credit'] ); ?> /> I rather not.<br />
		</label>
		<label for="<?php echo $this->get_field_id('credit'); ?>">
			<input name="<?php echo $this->get_field_name('credit'); ?>" type="radio" value="1" <?php checked( '1', $instance['credit'] ); ?> /> Sure, I would like to support a <a href="http://helsinkipromo.com">small business</a> from Finland.<br />
		</label>
	</p>
<?php		
	}
	// list = 1 clip = 0
	
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['username'] = $new_instance['username'];
		$instance['token'] = $new_instance['token'];
		$instance['mode'] = $new_instance['mode'];
		$instance['credit'] = $new_instance['credit'];
		return $instance;
	}

	// actual widget functionality
	function widget($args, $instance)
	{
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		$title = empty( $instance['title'] ) ? '' : apply_filters('widget_title', $instance['title']);
		$username = empty( $instance['username'] ) ? '' : $instance['username'];
		$token = empty( $instance['token'] ) ? '' : $instance['token'];
		$mode = $instance['mode'];
		$credit = $instance['credit'];

		// get lists from kippt
		if ( $mode == '1'){

			$ch = curl_init("https://kippt.com/api/lists/");

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$output = curl_exec( $ch );
			$obj = json_decode( $output, true );

			$data = $obj['objects'];
			$user_url = $data[0]['user'];
			curl_close($ch);

			// get user information, avatar url
			// function get_user_info()
			// {
			// 	$ch2 = curl_init('https://kippt.com' . $user_url);

			// 	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
			// 	curl_setopt($ch2, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
			// 	curl_setopt($ch2, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			// 	$curl_output = curl_exec( $ch2 );
			// 	$user = json_decode( $curl_output, true );

			// 	$avatar_url = $user["avatar_url"];
			// 	curl_close( $ch2 );
			// }

		} else { // get latest clips
			$ch3 = curl_init('https://kippt.com/api/clips/');
			curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch3, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
			curl_setopt($ch3, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

			$clips_output = json_decode( curl_exec( $ch3 ), true );
			$clips = $clips_output['objects'];
			curl_close( $ch3 );
		}

		function display_lists($data)
		{
			for ($i = 0; $i <= count($data) - 1; $i++){
                if ($data[$i]["is_private"] == true){
                    continue;
                } else {
                    echo "<div>";
                    echo "<li>";
                    echo "<a href='http://kippt.com";
                    echo $data[$i]['app_url'] . "'><span class='link-icon'></span>";
                    echo ucfirst(str_replace('-', ' ', $data[$i]['slug'] ) );
                    echo "</a></li></div>";
                }
        	}
		}

		function display_clips($clips){
			for ($i = 0; $i <= 4; $i++){
				echo "<div><li><a href='" . $clips[$i]['url'] . "'>";
				echo "<img src='" . $clips[$i]['favicon_url'] . "'class='kippt-favicon' />";
				echo $clips[$i]['title'];
				echo "</a></li></div>";
			}
		}

		// include widget.php
		include( plugin_dir_path(__FILE__) . '/views/widget.php' );
		wp_register_style( 'kippt', plugins_url( 'kippt-widget/css/widget.css') );
		wp_enqueue_style( 'kippt' );


		echo $after_widget;
	}

	public function register_widget_styles() {

		wp_register_style( 'KipptWidget-widget-styles', plugins_url( 'kippt-widget/css/widget.css' ) );
		wp_enqueue_style( 'KipptWidget-widget-styles' );

	} // end register_widget_styles
	
}
add_action( 'widgets_init', create_function( '', 'register_widget("KipptWidget");' ) );

?>