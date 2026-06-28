<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$title      = $component_data['title'] ?? '每日一言';
$color      = $component_data['color'] ?? '#e67e22';
$color_dark = $component_data['color_dark'] ?? '#ff9944';

function _ac_yiyan_fetch() {
    $slot = floor( current_time( 'timestamp' ) / 60 );
    $key  = 'ac_yiyan_' . $slot;
    $cached = get_transient( $key );
    if ( is_array( $cached ) && isset( $cached['saying'], $cached['source'] ) ) {
        return $cached;
    }
    $resp = wp_remote_get( 'https://v1.hitokoto.cn/', [
        'timeout' => 3, 'redirection' => 0,
    ] );
    $fallback = get_transient( 'ac_yiyan_last' );
    if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) !== 200 ) {
        return [
            'saying' => '万物皆有裂痕，那是光照进来的地方。',
            'source' => '佚名'
        ];
    }
    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( empty( $body['hitokoto'] ) ) {
        return [
            'saying' => '万物皆有裂痕，那是光照进来的地方。',
            'source' => '佚名'
        ];
    }
    $saying   = trim( $body['hitokoto'] );
    $from     = trim( $body['from'] ?? '' );
    $from_who = trim( $body['from_who'] ?? '' );

    $source = '';
    if ( $from !== '' ) {
        $source = $from;
        if ( $from_who !== '' ) $source .= ' · ';
        $source .= $from_who;
    } elseif ( $from_who !== '' ) {
        $source = $from_who;
    }
    // 无来源统一显示佚名
    if ( empty( $source ) ) $source = '佚名';

    $result = [ 'saying' => $saying, 'source' => $source ];
    set_transient( $key, $result, 90 );
    set_transient( 'ac_yiyan_last', $result, DAY_IN_SECONDS );
    return $result;
}
$data = _ac_yiyan_fetch();
$saying = $data['saying'];
$source_text = $data['source'];
?>
<style>
.ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> {
    background: #ffffff;
    padding: 24px 18px;
    border-radius: 10px;
    border-left: 3px solid <?php echo esc_attr( $color ); ?>;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    transition: transform 0.2s ease;
}
.ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?>:hover {
    transform: translateY(-2px);
}
.ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-title {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 12px 0;
    color: <?php echo esc_attr( $color ); ?>;
}
.ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-body {
    font-size: 15.5px;
    line-height: 1.85;
    color: #444;
    font-style: italic;
    position: relative;
    padding-left: 26px;
}
.ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-body::before {
    content: "\201C";
    font-family: Georgia, serif;
    font-size: 42px;
    color: <?php echo esc_attr( $color ); ?>;
    position: absolute;
    left: -4px;
    top: -10px;
    opacity: 0.35;
}
.ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-source {
    text-align: right;
    font-size: 13px;
    color: #aaa;
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px dashed #eee;
}

/* 系统深色适配 */
@media (prefers-color-scheme: dark) {
    .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> {
        background: #2c2c34 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        border-left-color: <?php echo esc_attr( $color_dark ); ?> !important;
    }
    .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-title {
        color: <?php echo esc_attr( $color_dark ); ?> !important;
    }
    .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-body {
        color: #e6e6ef !important;
    }
    .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-body::before {
        color: <?php echo esc_attr( $color_dark ); ?> !important;
    }
    .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-source {
        color: #888 !important;
        border-top: 1px dashed #444 !important;
    }
}

/* AeroCore html[data-theme="dark"] 统一深色卡片底色 */
html[data-theme="dark"] .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> {
    background: #2a2a2a !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
    border-left-color: <?php echo esc_attr( $color_dark ); ?> !important;
}
html[data-theme="dark"] .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-title {
    color: <?php echo esc_attr( $color_dark ); ?> !important;
}
html[data-theme="dark"] .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-body {
    color: #e6e6ef !important;
}
html[data-theme="dark"] .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-body::before {
    color: <?php echo esc_attr( $color_dark ); ?> !important;
}
html[data-theme="dark"] .ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?> .ac-yiyan-source {
    color: #888 !important;
    border-top: 1px dashed #444 !important;
}
</style>
<div class="ac-yiyan-wrap-<?php echo esc_attr( $component_id ); ?>" id="<?php echo esc_attr( $component_id ); ?>">
    <?php if ( $title ) : ?>
        <div class="ac-yiyan-title"><?php echo esc_html( $title ); ?></div>
    <?php endif; ?>
    <div class="ac-yiyan-body">
        <?php echo esc_html( $saying ); ?>
    </div>
    <!-- 直接固定输出出处，无if判断，永久显示 -->
    <div class="ac-yiyan-source">—— <?php echo esc_html( $source_text ); ?></div>
</div>