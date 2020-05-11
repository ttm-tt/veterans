<?php
return [
	'nestingLabel' => '{{hidden}}<label class="cell small-12 medium-3 middle" {{attrs}}>{{text}}</label>{{input}}',
	'inputContainer' => '<div class="grid-x grid-margin-x input {{type}}{{required}}">{{content}}</div>',
    'inputContainerError' => '<div class="grid-x grix-margin-x input {{type}}{{required}} error">{{content}}{{error}}</div>',	
    'error' => '<div class="cell large-6 medium-9 medium-offset-3 error-message">{{content}}</div>',
	'label' => '<label class="cell small-12 medium-3 middle" {{attrs}}>{{text}}</label>',
	'input' => '<input class="cell small-12 medium-9 large-6" type="{{type}}" name="{{name}}"{{attrs}}/>',
	'textarea' => '<textarea class="cell small-12 medium-9 large-6" name="{{name}}"{{attrs}}>{{value}}</textarea>',
	'select' => '<select class="cell small-12 medium-9 large-6 {{offset}}" name="{{name}}"{{attrs}}>{{content}}</select>',
	'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}"{{attrs}}>',
	'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}}>',
	'dateWidget' => '<span class="cell small-12 medium-9 large-6" {{attrs}}>{{year}}<span>&nbsp;&ndash;&nbsp;</span>{{month}}<span>&nbsp;&ndash;&nbsp;</span>{{day}}</span>',
    'file' => '<input class="cell small-12 medium-9 large-6" type="file" name="{{name}}"{{attrs}}>',

	// Own widgets
	// control translations
	'translate' => '{{input}}{{select}}'
];
