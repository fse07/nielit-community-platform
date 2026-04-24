<?php
namespace remoji;
defined( 'WPINC' ) || exit;


?>
<div class="remoji_bar">
	<?php if ( $emoji_list) : ?>
	<?php foreach ( $emoji_list as $emoji => $count ) : ?>
		<div class="remoji_container" data-remoji-id="<?php echo $remoji_id; ?>" data-remoji-type="<?php echo $remoji_type; ?>" data-remoji-name="<?php echo $emoji; ?>">
			<img src="<?php echo REMOJI_URL . 'data/emoji/' . $this->emoji( $emoji ) . '.svg'; ?>">
			<span class="remoji_count"><?php echo $count; ?></span>
		</div>
	<?php endforeach; ?>
	<?php endif; ?>

	<div class="remoji_add_container" data-remoji-id="<?php echo $remoji_id; ?>" data-remoji-type="<?php echo $remoji_type; ?>">
		<div class="remoji_add_icon"></div>
	</div>

	<div class="remoji_error_bar" data-remoji-id="<?php echo $remoji_id; ?>" data-remoji-type="<?php echo $remoji_type; ?>" style="display: none;"><?php echo __( 'Error happened.' , 'remoji' ); ?></div>
</div>