<?php //phpcs:disable ?>
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
<# } else { #>
	<span class="butterbean-description butterbean-no-info">
		<# if ( data.preview ) { #>
			<div class="image_preview dede">
				<i class="fas fa-eye"></i>
				<span data-preview="{{data.preview_url}}{{ data.preview }}.jpg">Preview</span>
			</div>
		<# } #>
	</span>
<# } #>

<input type="hidden" value="{{ data.value }}" {{{ data.attr }}} />

<div class="stm_checkbox_repeater">
	<!--<p>
		<input type="text" class="stm_checkbox_adder" placeholder="{{data.l10n.add_feature}}" />
		<button type="button" class="button button-primary butterbean-add-checkbox">{{data.l10n.add}}</button>
	</p>-->

	<div class="grouped_checkboxes_wrap">
		<# _.each( data.values, function( value, key) { #>
			<div class="grouped_checkboxes">
				<# if(value.group_title) { #>
				<h5>{{value.group_title}}</h5>
				<# } #>
				<# if(value.group_features) { #>
					<# _.each( value.group_features, function( feature, k) { #>
					<div class="stm_repeater_checkbox">
						<label>
							<input type="checkbox" data-key="{{key}}-{{k}}" <# if(feature.checked) { #> checked="checked" <# } #> />
							<span>{{feature.val}}</span>
						</label>
						<i class="fas fa-times" data-key="{{key}}-{{k}}"></i>
					</div>
					<# } ) #>
				<# } #>
			</div>
		<# } ) #>
	</div>
</div>

<a href="{{data.link}}" target="_blank">{{data.l10n.add_feature}}</a>
<?php //phpcs:enable ?>