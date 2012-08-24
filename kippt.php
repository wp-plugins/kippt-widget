<?php
/*
Plugin Name: Kippt widget for Wordpress
Plugin URI: http://helsinkipromo.com
Description: Displays all public lists or most recent items added to Kippt.
Author: Kenneth Blomqvist
Version: 0.3
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
			'window' => '1',
			'list' => '',
			'slug' => '',
			'credit' => '0' ) );

		$title = $instance['title'];
		$username = $instance['username'];
		$token = $instance['token'];
		$window = $instance['window'];
		$list = $instance['list'];
		$mode = $instance['mode'];
		$slug = $instance['slug'];

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
		
		<label for="<?php echo $this->get_field_id('mode'); ?>">
			<input name="<?php echo $this->get_field_name('mode'); ?>" type="radio" value="2" <?php checked( '2', $instance['mode'] ); ?> /> Show clips from list:<br />
		</label>
		<label for="<?php echo $this->get_field_id('list'); ?>">
			<input class="widefat" id="<?php echo $this->get_field_id('list'); ?>" name="<?php echo $this->get_field_name('list'); ?>" type="text" value="<?php echo esc_attr($slug); ?>" />
		</label>
		<small>(enter list name, e.g. "Good Reads")</small><br>
		Open links in new window:<br />

		<label for="<?php echo $this->get_field_id('window'); ?>">
			<input name="<?php echo $this->get_field_name('window'); ?>" type="radio" value="1" <?php checked( '1', $instance['window'] ); ?> /> Yes<br />
		</label>
		
		<label for="<?php echo $this->get_field_id('window'); ?>">
			<input name="<?php echo $this->get_field_name('window'); ?>" type="radio" value="0" <?php checked( '0', $instance['window'] ); ?> /> No<br />
		</label>
		
		If you like the widget, consider linking back to our site:<br />

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
	
	// save settings
	function update($new_instance, $old_instance)
	{	
		$username = $old_instance['username'];
		$token = $old_instance['token'];

		$list_name = str_replace(' ', '-', strtolower( $new_instance['list'] ) );

		$ch5 = curl_init("https://kippt.com/api/lists/$list_name");
		curl_setopt($ch5, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
		curl_setopt($ch5, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch5, CURLOPT_RETURNTRANSFER, 1);
		$json5 = curl_exec( $ch5 );
		curl_close( $ch5 );

		$json_obj = json_decode( $json5, true );
		$list_id = $json_obj["id"];

		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['username'] = $new_instance['username'];
		$instance['token'] = $new_instance['token'];
		$instance['mode'] = $new_instance['mode'];
		$instance['window'] = $new_instance['window'];
		$instance['list'] = $list_id;
		$instance['slug'] = str_replace(' ', '-', strtolower( $new_instance['list'] ) );
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
		$window = $instance['window'];
		$list_id = $instance['list'];
		$credit = $instance['credit'];
		$slug = $instance['slug'];



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


		} elseif ($mode == '0') { // get latest clips
			$ch3 = curl_init('https://kippt.com/api/clips/');
			curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch3, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
			curl_setopt($ch3, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

			$clips_output = json_decode( curl_exec( $ch3 ), true );
			$clips = $clips_output['objects'];
			curl_close( $ch3 );

		} else {
			// $list_name = str_replace(' ', '-', strtolower( $list ) );

			// $ch5 = curl_init("https://kippt.com/api/lists/$list_name");
			// curl_setopt($ch5, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
			// curl_setopt($ch5, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			// curl_setopt($ch5, CURLOPT_RETURNTRANSFER, 1);
			// $json5 = curl_exec( $ch5 );
			// curl_close( $ch5 );

			// $json_obj = json_decode( $json5, true );
			// $list_id = $json_obj["id"];

			$ch4 = curl_init("https://kippt.com/api/clips/?list=$list_id"); // ?list=$list_id
			curl_setopt($ch4, CURLOPT_HTTPHEADER, array( "X-Kippt-Username: $username", "X-Kippt-API-Token: $token" ) );
			curl_setopt($ch4, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch4, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($ch4, CURLOPT_POSTFIELDS, "list=$list_id" );

			$output4 = curl_exec( $ch4 );
			curl_close( $ch4 );
			$asd2 = json_decode($output4, true);
			$clips_by_list = $asd2['objects'];
		}

		function display_lists($data, $window)
		{
			if ( $new_window == '1'){		
				for ($i = 0; $i <= count($data) - 1; $i++){
	                if ($data[$i]["is_private"] == true){
	                    continue;
	                } else {
	                    echo "<div>";
	                    echo "<li>";
	                    echo "<a href='http://kippt.com";
	                    echo $data[$i]['app_url'] . "'";
                    	echo " target='_blank' > "; 
	                    echo "<span class='link-icon'></span>";
	                    echo ucfirst(str_replace('-', ' ', $data[$i]['slug'] ) );
	                    echo "</a></li></div>";
	                }
	        	}
	        } else {
	        	for ($i = 0; $i <= count($data) - 1; $i++){
	                if ($data[$i]["is_private"] == true){
	                    continue;
	                } else {
	                    echo "<div>";
	                    echo "<li>";
	                    echo "<a href='http://kippt.com";
	                    echo $data[$i]['app_url'] . "'>";
	                    echo "<span class='link-icon'></span>";
	                    echo ucfirst(str_replace('-', ' ', $data[$i]['slug'] ) );
	                    echo "</a></li></div>";
	                }
	        	}
	        }
		}

		function display_clips($clips, $window)
		{
			if ($window == '1'){
				for ($i = 0; $i <= 4; $i++){
					echo "<div><li><a href='" . $clips[$i]['url'] . "'";
	                echo " target='_blank' > ";
					echo "<img src='" . $clips[$i]['favicon_url'] . "' class='kippt-favicon' />";
					echo $clips[$i]['title'];
					echo "</a></li></div>";
				}
			} else {
				for ($i = 0; $i <= 4; $i++){
					echo "<div><li><a href='" . $clips[$i]['url'] . "'";
	                echo "> ";
					echo "<img src='" . $clips[$i]['favicon_url'] . "' class='kippt-favicon' />";
					echo $clips[$i]['title'];
					echo "</a></li></div>";
				}
			}
		}

		function display_clips_list($clips_by_list, $window){
			if ($window == '1'){
				for ($i = 0; $i <= 4; $i++){
					echo "<div><li><a href='" . $clips_by_list[$i]['url'] . "'";
	                echo " target='_blank' > ";
					echo "<img src='" . $clips_by_list[$i]['favicon_url'] . "' class='kippt-favicon' />";
					echo $clips_by_list[$i]['title'];
					echo "</a></li></div>";
				}
			} else {
				for ($i = 0; $i <= 4; $i++){
					echo "<div><li><a href='" . $clips_by_list[$i]['url'] . "'";
	                echo "> ";
					echo "<img src='" . $clips_by_list[$i]['favicon_url'] . "' class='kippt-favicon' />";
					echo $clips_by_list[$i]['title'];
					echo "</a></li></div>";
				}
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