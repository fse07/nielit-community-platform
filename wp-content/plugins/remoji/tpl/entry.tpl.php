<?php
namespace remoji;
defined( 'WPINC' ) || exit;


$menu_list = array(
	'setting'	=> __( 'Settings', 'remoji' ),
	'log'		=> __( 'Reaction Log', 'remoji' ),
);

?>
<div class="wrap remoji-settings">
	<h1 class="remoji-h1">
		Remoji
	</h1>
	<span class="remoji-desc">
		v<?php echo Core::VER; ?>
	</span>
	<hr class="wp-header-end">
</div>

<div class="remoji-wrap">
	<h2 class="remoji-header nav-tab-wrapper">
	<?php
		$i = 1;
		foreach ( $menu_list as $tab => $val ) {
			$accesskey = $i <= 9 ? "remoji-accesskey='$i'" : '';
			echo "<a class='remoji-tab nav-tab' href='#$tab' data-remoji-tab='$tab' $accesskey>$val</a>";
			$i ++;
		}
	?>
	</h2>

	<div class="remoji-body">
	<?php
		// include all tpl for faster UE
		foreach ($menu_list as $tab => $val) {
			echo "<div data-remoji-layout='$tab'>";
			require REMOJI_DIR . "tpl/$tab.tpl.php";
			echo "</div>";
		}
	?>
	</div>

</div>

<h2 style="margin: 30px;">
	<a href="https://wordpress.org/support/plugin/remoji/reviews/?rate=5#new-post" target="_blank"><?php echo __( 'Rate Us!', 'remoji' ); ?>
		<span class="wporg-ratings rating-stars" style="text-decoration: none;">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span>
		</span>
	</a>
</h2>