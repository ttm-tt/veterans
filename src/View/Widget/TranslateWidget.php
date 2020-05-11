<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;
use Cake\ORM\TableRegistry;

// A widget to support translatable strings
class TranslateWidget implements WidgetInterface {
	
    /**
     * StringTemplate instance.
     *
     * @var \Cake\View\StringTemplate
     */
    protected $_templates;
	
	/**
	 * SelectBoxWidget instance.
	 * Renders a select box to switch between translation languages
	 * 
	 * @var \Cake\View\Widget\SelectBoxWidget
	 */
	protected $_select;
	
	/**
	 * TextWidget
	 * The input widgets
	 * 
	 * @var \Cake\View\Widget\BasicWidget
	 */
	protected $_text;
	
	/**
	 * TextareaWidget
	 * The input widgets
	 * 
	 * @var \Cake\View\Widget\TextareaWidget
	 */
	protected $_textarea;

	/**
     * Constructor.
     *
     * @param \Cake\View\StringTemplate $templates Templates list.
     */
    public function __construct($templates, $select, $text, $textarea)
    {
        $this->_templates = $templates;
		$this->_select = $select;
		$this->_text = $text;
		$this->_textarea = $textarea;
    }

	public function render(array $data, ContextInterface $context): string {
		$langTable = TableRegistry::get('Languages');
		$langs = $langTable->find('list', array('fields' => ['name', 'description']))->toArray();
		// No English, this is the default
		unset($langs['en']);
		
		// TODO: How to correctly build all options?
		// And how to avoid unsetting options and type?
		// We can't use type because we have our own type, but we need it for the
		// various input fields
		$data += [
			'_select' => [],
			'_input' => [],
			'id' => $data['name'],
		];
		
		unset($data['options']);		
		unset($data['type']);
		
		// The onchange script to change visibility
		$onchange = 
				'$(this).parent().find("input, textarea").hide(); ' .
				'if ($(this).val() == "") $(this).parent().find("#' . $data['id'] . '").show(); ' .
				'else $(this).parent().find("#_translations-" + $(this).val() + "-' . $data['id'] . '").show(); '
		;
		
		// Special options for the select (language) field
		// It would be better if the templateVars could be avoided and the 
		// offset part of the template. But I can't make it use its own template
		// XXX: Is there a way to have an owner-defined template instead of the 
		// stock 'select' template?
		$selectOptions = $data['_select'] + [
			// name must not be set or it will overwrite the input field
			'name' => false,       
			'options' => $langs,
			'empty' => 'English',
			'templateVars' => ['offset' => 'medium-offset-3'],
			'onchange' => $onchange,
			// 'escape' => false			
		];
		
		// Options for the input fields
		$inputOptions = $data['_input'] + [
			
		];
		
		unset($data['_select']);
		unset($data['_input']);
		
		// Input field can be text or textarea, it depends on the column type
		// Since we are translating it can't be number or the like
		$_textx = $context->type($data['name']) === 'text' ? $this->_textarea : $this->_text;
		
		// Render <select> and main <input> fields
		$select = $this->_select->render($selectOptions + $data, $context);
		$input  = $_textx->render($inputOptions + $data, $context);
		
		// And for each language render the specific <input> field
		foreach (array_keys($langs) as $lang) {
			// I need the same ID as in the onchange method, but it doesn't have
			// to be what CakePHP would normally construct.
			// If we start using special chars we have to fallback to 
			// Cake\View\Helper\IdGeneratorTrait
			$id = '_translations-' . $lang . '-' . $data['id'];
			
			// $name is already in the form with brackets, but we need the parts of it
			// So we convert it back to the dotted form and then to an array
			$name = $this->_makeFieldNameFromName($data['name']);
			
			// Insert _translations.${lang} before the last item, so we have
			// sthg like '<tables>._translations.<lang>.column
			$parts = explode('.', $name);			
			array_splice($parts, count($parts) - 1, 0, ['_translations', $lang]);
			
			// The name must be what CakePHP expects, the parts separated with brackets.
			// Unfortunately CakePHP does this in code, there is no trait for it
			// The form is <table>[_translations][<lang>][columns]
			$name = $parts[0] . '[' . implode('][', array_slice($parts, 1)) . ']';
			
			$input .= $_textx->render([
					'id' => $id, 
					'name' => $name, 
					// Current value
					'val' => $context->val(implode('.', $parts)),
					// At start hide it, jQuery changes visibility
					'style' => 'display: none'
				] + $data + $inputOptions, $context
			);
		}
		
		// And now put all together into our translate widget
		return $this->_templates->format('translate', [
			'select' => $select,
			'input' => $input,
			'attrs' => $this->_templates->formatAttributes($data),
			'templateVars' => $data['templateVars']
		]);
	}

	// Returns an array of all fields which needs to be secured for the SecurityComponent
	public function secureFields(array $data): array {
        if (!isset($data['name']) || $data['name'] === '') {
            return [];
        }

		$langTable = TableRegistry::get('Languages');
		$langs = $langTable->find('list', array('fields' => ['name', 'description']))->toArray();
		// No English, this is the default
		unset($langs['en']);
		
		// First entry is the main input
		$ret = [$data['name']];
		
		// And then all inputs for the translations
		foreach (array_keys($langs) as $lang) {
			$ret[] = $this->_makeTranslationNameFromName($data['name'], $lang);
		}
		
		return $ret;		
	}
	
	// $data does not contain the fieldName but the name used for POST
	// But for translations we have to split it and we do that by transforming it back
	private function _makeFieldNameFromName($name) {
		$name = str_replace('][', '.', $name);
		$name = str_replace('[', '.', $name);
		$name = str_replace(']', '.', $name);
		
		// If name ended with a ']' then we'd have an unwanted '.' at the end
		if (strlen($name) > 0 && substr($name, -1) === '.')
			$name = substr($name, 0, -1);
		
		return $name;
	}
	
	// Insert _translations.$lang into the name
	private function _makeTranslationNameFromName($name, $lang) {
		$name = $this->_makeFieldNameFromName($name);
		$parts = explode('.', $name);
		array_splice($parts, count($parts) - 1, 0, ['_translations', $lang]);

		return 	$parts[0] . '[' . implode('][', array_slice($parts, 1)) . ']';
	}	
}
