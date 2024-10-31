<?php
/**
 * Custom post type UI class
 *
 * @package PSV\classes\cpt
 */

/**
 * The admin UI class. Loads our plugin custom post type UI.
 */
class PSV_CPT_UI {

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Holds array of types of content available for a user to insert.
	 *
	 * @var array
	 */
	public $type_options = array(
		'Insert...'         => '',
		'Post or Page'      => 'Post',
		'Shortcode'         => 'Shortcode',
		'Full Screen Video' => 'YouTube',
		'Full Screen Image' => 'Image',
		'Insert My Own'     => 'Insert',
	);

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
	 * Intentionally left blank
	 */
	function __construct() {}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function split_view_ui( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'premise_split_view', 'premise_split_view_nonce' );
		?>
		<div class="premise-ui-intro">
			<p>Insert the content you would like to display on each side of the Split View.
			<br>To insert this Split View anywhere in your site use the following shortcode <code>[pwp_splitview id="<?php echo $post->ID; ?>"]</code></p>
		</div>
		<div class="premise-row premise-relative">
			<div class="col2 premise-align-center">
				<div class="psv-cpt-ui psv-ui-left">
					<?php $this->select_type( 'left' ); ?>
				</div>
			</div>

			<div class="psv-ui-separator premise-absolute"></div>

			<div class="col2 premise-align-center">
				<div class="psv-cpt-ui psv-ui-right">
					<?php $this->select_type( 'right' ); ?>
				</div>
			</div>

		</div>
		<div class="premise-ui-color">
			<p>Change the color of the Split View controls.</p>
		</div>
		<div class="premise-row">
			<?php pwp_field( array(
					'type'          => 'wp_color',
					'default'       => '#1652db', // Default blue.
					'name'          => 'premise_split_view[color]',
					'wrapper_class' => 'span5',
					'context'       => 'post',
				)
			); ?>
		</div>
		<?php
	}

	/**
	 * Insert the selct type fields
	 *
	 * The first step in creating a split view
	 *
	 * @param  string $side which side fields belong to.
	 * @return string       html for fields for left or right side.
	 */
	public function select_type( $side = 'left' ) {
		premise_field( 'select', array(
			'context' => 'post',
			'name'    => 'premise_split_view['.$side.'][type]',
			'options' => $this->type_options,
		));

		echo '<div class="psv-ui-insert premise-relative">';
			$this->insert_content( $side );
		echo '</div>';
	}

	/**
	 * Insert content fields
	 *
	 * @param string $side the side to load the content for
	 *
	 * @return string html for insert content sections.
	 */
	public function insert_content( $side = 'left' ) {
		$_types = array(
			'Post'      => 'select',
			'Shortcode' => 'text',
			'YouTube'   => 'video',
			'Image'     => 'wp_media',
			'Insert'    => 'textarea',
		);

		$html = '';

		foreach ( $_types as $k => $v ) {
			$args = array(
				'context' => 'post',
				'name'    => 'premise_split_view['.$side.']['.$k.']',
				'type'    => $v,
			);

			if ( 'Post' == $k ) {
				$args['options'] = $this->get_post_options();
			}

			if ( 'YouTube' == $k ) {
				$args['placeholder'] = 'Video ID or URL - YouTube, Vimeo or Wistia';
			}

			$html .= '<div class="psv-insert-content premise-absolute psv-insert-' . $k;
			$html .= $k == premise_get_value( 'premise_split_view[' . $side . '][type]', 'post' ) ? ' psv-content-active">' : '">';

				if ( 'Insert' == $k ) {
					$args['class'] = 'premise-hidden';

					$html .= '<a href="javascript:;" class="premise-btn pwpsv-edit-insert">Edit Content</a>';
				}

				$html .= pwp_field( $args, false );

			$html .= '</div>';
		}

		echo $html;
	}

	/**
	 * Get a list of all post and pages for our select dropdown
	 *
	 * @return array all posts and pages in array format: post_title => id
	 */
	protected function get_post_options() {
		$_posts = get_posts( array(
			'post_type'     => array( 'post', 'page' ),
			'post_status'   => 'publish',
			'posts_er_page' => -1
		) );

		$options = array();
		$options['Select a Post/Page..'] = '';
		foreach ( $_posts as $k => $v ) {
			$options[ $v->post_title ] = $v->ID;
		}
		return $options;
	}

	/**
	 * Insert the modal usede to insert content using the WYSIWYG editor in the aplit view.
	 *
	 * @return string the html for the modal
	 */
	public function insert_footer() {
		global $post;
		$post_types = array( 'premise_split_view' );

		$html = '';
		if ( $post
			&& in_array( $post->post_type, $post_types ) ) {
			ob_start();
			?>
			<div id="pwpsv-modal" style="display: none;">
				<div class="pwpsv-modal-overlay">
					<div class="pwpsv-modal-wrapper">
						<?php wp_editor( '', 'pwpsv_insert_editor' ); ?>
						<div class="premise-clear"><br></div>
						<?php premise_field( 'submit', array( 'id' => 'pwpsv-insert-content', 'wrapper_class' => 'premise-inline-block premise-float-left' ) ); ?>
						<?php premise_field( 'button', array( 'id' => 'pwpsv-insert-cancel', 'value' => 'cancel', 'wrapper_class' => 'premise-inline-block premise-float-right' ) ); ?>
						<div class="premise-clear"></div>
					</div>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
		}
		echo $html;
	}
}
