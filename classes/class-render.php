<?php
/**
 * The Render Class
 *
 * @package PSV
 * @subpackage PSV\classes\render
 */


/**
* Render the Split View
*
* This class is called when the split view custom type is loaded
* in the front end. It displays the split view by calling the
* split view shortcode.
*/
class PSV_Render_View {

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 *
	 * @var object
	 */
	protected static $instance = null;



	/**
	 * Holds the post id for the split view
	 *
	 * @var string
	 */
	protected $id = '';




	/**
	 * Access this class working instance
	 *
	 * @since   1.0
	 * @return  object instance of this class
	 */
	public static function get_instance() {
		null === self::$instance and self::$instance = new self;

		return self::$instance;
	}




	/**
	 * intentionally left blank
	 */
	function __construct() {}



	/**
	 * Checks that we are in the right post type and displays the view.
	 *
	 * @param string $content the current content being loaded by Wordpress
	 *
	 * @return string shortcode html for view or the content that was going to be loaded any ways.
	 */
	public function init( $content ) {

		global $post;

		if ( 'premise_split_view' == $post->post_type ) {

			$this->id = $post->ID;

			return do_shortcode( '[pwp_splitview id="'.$this->id.'"]' );
		}

		else {
			return $content;
		}
	}
}

?>