<?php // @codingStandardsIgnoreStart ?>
<# if ( data.label ) { #>
<span class="butterbean-label">{{ data.label }}</span>
<# } #>

<# if ( data.description ) { #>
<span class="butterbean-description">
		{{{ data.description }}}
		<# if ( data.preview ) { #>
			<div class="image_preview">
				<i class="fas fa-eye"></i>
				<span data-preview="{{data.preview_url}}{{ data.preview }}.jpg">Preview</span>
			</div>
		<# } #>
	</span>
<# } #>
<# _.each( data.values, function( group, gkey) { #>

<div class="stm_repeate_group_wrapper">
	<div class="stm-btn-remove-wrapper">
		<i class="butterbean-remove-group fa fa-trash" data-gkey="{{gkey}}" title="<?php echo esc_html__( 'Remove Group', 'stm_vehicles_listing' ) ?>"></i>
	</div>
	<div class="stm-btns-move-wrapper">
		<# if(gkey != 0) { #>
		<i class="butterbean-move-group-up fa fa-arrow-up" data-gkey="{{gkey}}" title="<?php echo esc_html__( 'Move Group Up', 'stm_vehicles_listing' ) ?>"></i>
		<# } #>
		<# if(parseInt(data.values.length - 1) != gkey) { #>
		<i class="butterbean-move-group-down fa fa-arrow-down" data-gkey="{{gkey}}" title="<?php echo esc_html__('Move Group Down', 'stm_vehicles_listing') ?>"></i>
		<# } #>
	</div>
	<div class="stm-group-data-wrapper">
		<div>
			<h5>Group Icon</h5>
			<div class="stm_form_wrapper">
				<div class="stm_info_group_icon">
					<input type="hidden" name="{{ data.field_name }}[{{gkey}}][icon]" value="{{group.icon}}" data-gkey="{{gkey}}" {{{ data.attr }}} />
					<div class="icon">
						<img src="<?php echo STM_LISTINGS_URL; ?>/assets/images/plus.svg" class="stm-default-icon_" />
						<# if(group.icon != '') { #>
						<i class="{{group.icon}}"></i>
						<# } #>
					</div>
					<# if(group.icon != '') { #>
					<i class="stm_delete_icon fa fa-trash"></i>
					<# } #>
				</div>
			</div>
		</div>
		<div class="group-title-input">
			<h5>Group Title</h5>
			<input type="text" name="{{ data.field_name }}[{{gkey}}][main_title]" value="{{group.main_title}}" data-gkey="{{gkey}}" {{{ data.attr }}}/>
		</div>
	</div>
	<div class="stm_repeater_info_inputs">
		<# _.each( group.fields, function( value, key) { #>
		<div class="info_inputs_wrapper">
			<div>
				<h5>Title</h5>
				<input type="text" name="{{ data.field_name }}[{{gkey}}][fields][{{key}}][item_title]" value="{{value.item_title}}" data-key="{{key}}" data-gkey="{{gkey}}" data-name="k" {{{ data.attr }}}/>
			</div>
			<div>
				<h5>Value</h5>
				<input type="text" name="{{ data.field_name }}[{{gkey}}][fields][{{key}}][item_val]" value="{{value.item_val}}" data-key="{{key}}" data-gkey="{{gkey}}" data-name="v" {{{ data.attr }}}/>
			</div>
			<i class="fas fa-times butterbean-delete-info-field" data-group="{{gkey}}" data-delete="{{key}}"></i>
		</div>
		<# } ) #>
	</div>
	<button type="button" class="button button-primary butterbean-add-info-field" data-key="{{gkey}}">Add Fields</button>
</div>
<# } ) #>
<div class="stm-add-info-group-btn">
	<button type="button" class="button button-primary butterbean-add-info-group">Add Group</button>
</div>

<?php
if ( function_exists( 'stm_vehicles_listing_get_icons_html' ) ) {
	stm_vehicles_listing_get_icons_html();
}
// @codingStandardsIgnoreEnd
