<?php

$finder = PhpCsFixer\Finder::create()
	->notName('*.blade.php')
	->exclude([
        'bootstrap/cache',
        'node_modules',
        'storage'
    ])
	->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
		'array_indentation' => true,
        'array_syntax' => true,
		'binary_operator_spaces' => true,
		'braces' => [
			'position_after_functions_and_oop_constructs' => 'same'
		],
		'clean_namespace' => true,
		'concat_space' => true,
		'constant_case' => true,
		'elseif' => true,
		'encoding' => true,
		'full_opening_tag' => true,
		'function_declaration' => true,
		'function_typehint_space' => true,
		'indentation_type' => true,
		'linebreak_after_opening_tag' => true,
		'line_ending' => true,
		'list_syntax' => true,
		'lowercase_cast' => true,
		'lowercase_keywords' => true,
		'lowercase_static_reference' => true,
		'magic_method_casing' => true,
		'magic_constant_casing' => true,
		'multiline_whitespace_before_semicolons' => true,
		'native_function_casing' => true,
		'native_function_type_declaration_casing' => true,
		'no_alternative_syntax' => true,
		'no_blank_lines_after_class_opening' => true,
		'no_closing_tag' => true,
		'no_empty_statement' => true,
		'no_leading_import_slash' => true,
		'no_mixed_echo_print' => true,
		'no_singleline_whitespace_before_semicolons' => true,
		'no_spaces_after_function_name' => true,
		'no_space_around_double_colon' => true,
		'no_spaces_around_offset' => true,
		'no_spaces_inside_parenthesis' => true,
		'no_trailing_comma_in_singleline' => true,
		'no_trailing_whitespace' => true,
		'no_trailing_whitespace_in_comment' => true,
		'no_unused_imports' => true,
		'no_whitespace_before_comma_in_array' => true,
		'no_whitespace_in_blank_line' => true,
		'normalize_index_brace' => true,
		'not_operator_with_successor_space' => true,
		'object_operator_without_whitespace' => true,
		'ordered_imports' => true,
		'short_scalar_cast' => true,
		'single_quote' => true,
		'standardize_not_equals' => true,
		'statement_indentation' => true,
		'ternary_operator_spaces' => true,
		'ternary_to_null_coalescing' => true,
		'trim_array_spaces' => true,
		'unary_operator_spaces' => true,
		'visibility_required' => true,
		'whitespace_after_comma_in_array' => true
    ])
	->setRiskyAllowed(true)
	->setIndent("\t")
    ->setFinder($finder);
