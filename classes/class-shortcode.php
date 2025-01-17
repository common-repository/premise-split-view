<?php
/**
 * Shortcode class
 *
 * @package PSV\classes\shortcode
 */

/**
 * The shortcode class. Loads and registers our plugin's shortcode.
 */
class PSV_Shortcode {

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 *
	 * @var object
	 */
	protected static $instance = null;



	/**
	 * Holds the shortcode attributes
	 *
	 * @var array
	 */
	public $atts = array();




	/**
	 * Holds the options for each split view
	 *
	 * @var array
	 */
	public $split_view = array();




	/**
	 * Holds HTML string for this shortcode
	 *
	 * @var string
	 */
	public $html = '';




	/**
	 * Access this plugin’s working instance
	 *
	 * @since   1.0
	 * @return  object instance of this class
	 */
	public static function get_instance() {
		null === self::$instance and self::$instance = new self;

		return self::$instance;
	}




	/**
	 * Intentionally left blank.
	 */
	function __construct() {}



	/**
	 * Initiate our class. Gets shortcode atts and if id exists it builds
	 * our object and split view. Ohterwise, it retunrs an error message saying
	 * that the id is required.
	 *
	 * @param array $atts array of attributes for the shortcode.
	 *
	 * @return string html for the split view or error message
	 */
	public function init( $atts ) {

		$this->atts = shortcode_atts( array(
	        'id' => '',
	    ), $atts, 'pwp_splitview' );

	    // First, check if there is an id.
		if ( isset( $this->atts['id'] ) && ! empty( $this->atts['id'] ) ) {
			$this->prepare();
			return $this->output();
		}
		else
			return '<p>You must provide an <code>id</code> in order to properly display a Split View.</p>';
	}



	/**
	 * Gets the split view data and builds the view if data not empty
	 */
	protected function prepare() {

		// Get the split view.
		$this->split_view = premise_get_value( 'premise_split_view', array( 'context' => 'post', 'id' => (int) $this->atts['id'] ) );

		if ( $this->split_view && ! empty( $this->split_view ) ) {

			$this->left  = ( isset( $this->split_view['left'] ) && ! empty( $this->split_view['left'] ) )   ? $this->split_view['left']  : array();
			$this->right = ( isset( $this->split_view['right'] ) && ! empty( $this->split_view['right'] ) ) ? $this->split_view['right'] : array();

			$this->build();
		}
	}



	/**
	 * Builds the split view
	 *
	 * @return string html for split view
	 */
	protected function build() {
		$_html = '<div class="psv-compare-wrapper">
			<div class="psv-compare-inner">';
				// Get right and left side views.
				foreach( $this->split_view as $side => $view ) {
					// Get content if type exists and is not empty.
					if ( isset( $view['type'] ) && ! empty( $view['type'] ) )
						$_html .= $this->get_view( $side );
				}
		$_html .= '</div>
			</div>';

		$this->html = $_html;
	}




	/**
	 * Get each view. Left or Right side.
	 *
	 * @param  string $side left or right. determines which side to get.
	 * @return string       html for one side.
	 */
	protected function get_view( $side ) {

		if ( empty( $side ) || ! is_string( $side ) ) {
			return false;
		}

		$view = ( 'left' == $side ) ? $this->left : $this->right;

		$inline_style = '';

		if ( isset( $this->split_view['color'] )
			&& $this->split_view['color'] !== '#1652db' ) {
			$inline_style = ' style="background-color:' . esc_attr( $this->split_view['color'] ) . ';"';
		}

		$handle = '<div class="psv-compare-handle"' . $inline_style . '>
			<a href="javascript:;" class="psv-slide-left"><i class="fa fa-caret-left"></i></a>
			<a href="javascript:;" class="psv-slide-right"><i class="fa fa-caret-right"></i></a>
		</div>';

		$_view = '';

		if ( isset( $view['type'] ) && ! empty( $view['type'] ) ) {

			$_view = '<div class="psv-compare-it psv-compare-' . esc_attr( $side );

			$_view .= ( 'right' == $side ) ? ' psv-compare-front" style="background: #fff;">' . $handle : '">';

			$_view .= '<div class="psv-compare-it-inner">';

			// Get the content for each view.
			$_view .= '<div class="psv-content">' . $this->content( $view ) . '</div>';

			$_view .= '</div></div>';
		}

		return $_view;
	}




	/**
	 * Get content depending on the type
	 *
	 * @param  array  $view left or right view data ( type => content ).
	 * @return string       html for content
	 */
	protected function content( $view = array() ) {
		$type = isset( $view['type'] ) && ! empty( $view['type'] ) ? $view['type'] : '';
		$cont = isset( $view[$type] ) && ! empty( $view[$type] )   ? $view[$type]  : '';

		switch ( $type ) {
			// Get a post
			case 'Post':
				$_html = $this->post( $cont );
				break;

			// Get a YouTube or Vimeo Video.
			case 'YouTube':
				$_html = $this->youtube( $cont );
				break;

			// Get an image.
			case 'Image':
				$_html = $this->image( $cont );
				break;

			// Get a Shortcode.
			case 'Shortcode':
				$_html = do_shortcode( $cont );
				break;

			// Return empty string as default.
			default:
				$_html = $this->insert_content( $cont );
				break;
		}

		return $_html;
	}



	/**
	 * Returns the content for a post
	 *
	 * @todo Find a way to load the page template and the post template from the theme instead of outputting the content.
	 *
	 * @param  string|int $id id of post to retreive.
	 * @return string     html for content. or empty string
	 */
	protected function post( $id = '' ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return '';
		}

		$_html = '';

		$post = (object) get_post( $id );

		$_html = '<div class="psv-content-post">
			<div class="psv-post-title">
				<h3>' . wp_kses_data( $post->post_title ) . '</h3>
			</div>
			<div class="psv-post-content">' . wpautop( wptexturize( $post->post_content ) ) . '</div>
		</div>';

		return $_html;
	}




	/**
	 * Get a youtube video
	 *
	 * @param  string $video video id.
	 * @return string        html for video
	 */
	protected function youtube( $video = '' ) {

		if ( empty( $video ) || ! is_string( $video ) ) {
			return '';
		}

		$_html = '<div class="psv-content-video">' . premise_output_video( $video ) . '</div>';

		return $_html;
	}



	/**
	 * Get an image
	 *
	 * @param  string $url url for image.
	 * @return string      div with image as background
	 */
	protected function image( $url = '' ) {
		if ( empty( $url ) || ! is_string( $url ) ) {
			return '';
		}

		$_html = '<div class="psv-content-image" style="background-image: url('.$url.');"></div>';

		return $_html;
	}


	protected function insert_content( $content = '' ) {
		if ( ! empty( $content ) ) {
			$_html = '<div class="psv-content-post">
				<div class="psv-post-content">' . wpautop( wptexturize( $content ) ) . '</div>
			</div>';
		}
		else {
			$_html = 'No content was inserted';
		}

		return $_html;
	}



	/**
	 * Output the shortcode
	 *
	 * @return string the shortcode's html
	 */
	public function output() {
		if ( '' !== $this->html ) {
			return $this->html;
		}

		return '<p>Looks like there was an issue building the Split View.</p>';
	}
}
