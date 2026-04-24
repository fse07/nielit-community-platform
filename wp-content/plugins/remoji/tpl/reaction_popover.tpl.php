<?php
namespace remoji;
defined( 'WPINC' ) || exit;

$list = $this->emoji();
$div_max_height = count( $list ) / 9 * 33;
?>

<div id="remoji_panel">
	<div class="remoji_picker_list">
		<div style="overflow: visible; height: 0px; width: 0px;">
			<div class="remoji_picker_list_scroller" style="height: <?php echo $div_max_height; ?>px;">
				<div class="" style="width: auto; height: <?php echo $div_max_height; ?>px; max-width: 352px; max-height: <?php echo $div_max_height; ?>px; overflow: hidden; position: relative;">
				<?php $i = 0; foreach ( $list as $word => $emoji ) : ?>
					<?php if ( $i % 9 == 0 ) : ?>
					<div class="remoji_picker_row" style="top: <?php echo floor( $i / 9 ) * 33 + 3; ?>px">
					<?php endif; ?>
					<?php
						echo sprintf(
							'<div data-remoji-color="%1$s" data-remoji-name="%2$s" class="remoji_picker_item"><img src="%3$s" alt="%2$s"></div>',
							$i % 6,
							$word,
							REMOJI_URL . 'data/emoji/' . $emoji . '.svg'
						);
					 ?>
					<?php if ( $i % 9 == 8 ) : ?>
					</div>
					<?php endif; ?>
				<?php $i++; endforeach; ?>

				</div>
			</div>
		</div>
	</div>
	<div class="remoji_picker_footer">
		<div id="remoji_preview"></div>
		<div id="remoji_preview_text"></div>
		<div class="remoji_picker_handy">
			<?php $i = 0; foreach ( $this->emoji_handy() as $word ) : ?>
			<?php
				echo sprintf(
					'<div data-remoji-color="%1$s" data-remoji-name="%2$s" class="remoji_picker_item"><img src="%3$s" alt="%2$s"></div>',
					$i % 6,
					$word,
					REMOJI_URL . 'data/emoji/' . $this->emoji( $word ) . '.svg'
				);
			 ?>
			<?php $i++; endforeach; ?>
		</div>
	</div>
</div>