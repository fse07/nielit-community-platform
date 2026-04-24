<?php
/**
 * Widget class
 *
 * @since 1.0
 */
namespace remoji\widgets;
defined( 'WPINC' ) || exit;

use remoji\GUI;
use remoji\Reaction;

/**
 * Widget last reacted
 */
class Last_Reacted extends \WP_Widget {
	const CONF_TPL = '<img src="{emoji}" class="remoji_widget_img"> {comment_prefix} <a href="{link}">{title}</a>';
	const CONF_LIMIT = 10;

	public function __construct() {
		parent::__construct(
			'last_reacted',
			__('Recent Reacted Emoji', 'remoji' ),
			array( 'description' => __('Remoji last reacted posts/comments list', 'remoji' ) )
		);

	}

	public function widget( $args, $instance ) {
		echo $args[ 'before_widget' ];

		if ( ! empty( $instance[ 'title' ] ) ) {
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}

		echo '<ul>';

		$list = Reaction::get_instance()->history_list( (int) $instance[ 'limit' ], 0 );
		$__gui = GUI::get_instance();
		if ( $list ) {
			foreach ( $list as $v ) {
				$tpl = $instance[ 'tpl' ];
				$tpl = str_replace( '{emoji}', REMOJI_URL . 'data/emoji/' . $__gui->emoji( $v->emoji ) . '.svg', $tpl );

				if ( $v->related_type == 'post' ) {
					$tpl = str_replace( '{link}', get_post_permalink( $v->related_id ), $tpl );
					$tpl = str_replace( '{title}', get_the_title( $v->related_id ), $tpl );
					$tpl = str_replace( '{comment_prefix}', '', $tpl );
				}
				elseif ( $v->related_type == 'comment' ) {
					$comment = get_comment( $v->related_id );
					$tpl = str_replace( '{link}', get_comment_link( $v->related_id ), $tpl );
					$tpl = str_replace( '{title}', get_the_title( $comment->comment_post_ID ), $tpl );
					$tpl = str_replace( '{comment_prefix}', __( 'Comment on', 'remoji' ), $tpl );
				}
				else {
					continue;
				}

				echo '<li>' . $tpl . '</li>';
			}
		}
		else {
			echo '<li>' . 'N/A' . '</li>';
		}

		echo '</ul>';

		echo $args[ 'after_widget' ];
	}

	public function form( $instance ) {
		$title = ! empty( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'Recent Reacted', 'remoji' );
		$tpl = ! empty( $instance[ 'tpl' ] ) ? $instance[ 'tpl' ] : self::CONF_TPL;
		$limit = ! empty( $instance[ 'limit' ] ) ? absint( $instance[ 'limit' ] ) : self::CONF_LIMIT;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'remoji' ); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'tpl' ) ); ?>"><?php echo esc_html__( 'Template', 'remoji' ); ?>:</label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'tpl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tpl' ) ); ?>" type="text" cols="30" rows="5"><?php echo esc_textarea( $tpl ); ?></textarea>
			<span class="description"><?php echo __( 'Supported tags', 'remoji' ); ?>: <code>{link}</code> <code>{title}</code> <code>{emoji}</code> <code>{comment_prefix}</code></span>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php echo esc_html__( 'Limit', 'remoji' ); ?>:</label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" size="3" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<?php

	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance[ 'title' ] = ! empty( $new_instance[ 'title' ] ) ? strip_tags( $new_instance[ 'title' ] ) : '';
		$instance[ 'tpl' ] = ! empty( $new_instance[ 'tpl' ] ) ? $new_instance[ 'tpl' ] : self::CONF_TPL;
		$instance[ 'limit' ] = ! empty( $new_instance[ 'limit' ] ) ? (int) $new_instance[ 'limit' ] : self::CONF_LIMIT;

		return $instance;
	}

}
