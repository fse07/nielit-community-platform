<?php
namespace remoji;
defined( 'WPINC' ) || exit;

$__gui = GUI::get_instance();
$__reaction = Reaction::get_instance();

$list = $__reaction->history_list( 20 );
$count = $__reaction->count_list();
$pagination = Util::pagination( $count, 20 );
?>
<h3 class="remoji-title-short">
	<?php echo __( 'Reaction Log', 'remoji' ); ?>
</h3>

<?php echo __( 'Total', 'remoji' ) . ': ' . $count; ?>

<?php echo $pagination; ?>

<table class="wp-list-table widefat striped">
	<thead>
	<tr>
		<th>#</th>
		<th><?php echo __( 'Date', 'remoji' ); ?></th>
		<th><?php echo __( 'IP', 'remoji' ); ?></th>
		<th width="40%"><?php echo __( 'GeoLocation', 'remoji' ); ?></th>
		<th><?php echo __( 'Reaction', 'remoji' ); ?></th>
		<th><?php echo __( 'Operation', 'remoji' ); ?></th>
		<th><?php echo __( 'Related Topic', 'remoji' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $list as $v ) : ?>
		<tr>
			<td><?php echo $v->id; ?></td>
			<td><?php echo Util::readable_time( $v->dateline ); ?></td>
			<td><?php echo $v->ip; ?></td>
			<td><?php echo $v->ip_geo; ?></td>
			<td>
				<img src="<?php echo REMOJI_URL . 'data/emoji/' . $__gui->emoji( $v->emoji ) . '.svg'; ?>" height='30px' />
			</td>
			<td>
				<a href="<?php echo Util::build_url( Router::ACTION_REACTION, Reaction::TYPE_DEL, false, null, array( 'remoji_id' => $v->id ) ); ?>" class="button remoji-btn-danger"><?php echo __( 'Delete', 'remoji' ); ?></a>
			</td>
			<td>
				<?php if ( $v->related_type == 'post' ) : ?>
					<a href="<?php echo get_post_permalink( $v->related_id ); ?>" target="_blank"><?php echo get_the_title( $v->related_id ); ?></a>
				<?php elseif ( $v->related_type == 'comment' ): ?>
					<?php $comment = get_comment( $v->related_id ); ?>
					<?php echo __( 'Comment on', 'remoji' ); ?>
					<a href="<?php echo get_comment_link( $v->related_id ); ?>" target="_blank"><?php echo get_the_title( $comment->comment_post_ID ); ?></a>
				<?php else: ?>
					<?php echo $v->related_type; ?>
					<?php echo $v->related_id; ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php echo $pagination; ?>