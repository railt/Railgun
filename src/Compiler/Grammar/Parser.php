<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Compiler\Grammar;

use Railt\Lexer\Factory;
use Railt\Lexer\LexerInterface;
use Railt\Parser\Driver\Llk;
use Railt\Parser\Driver\Stateful;
use Railt\Parser\Grammar;
use Railt\Parser\GrammarInterface;
use Railt\Parser\ParserInterface;
use Railt\Parser\Rule\Alternation;
use Railt\Parser\Rule\Concatenation;
use Railt\Parser\Rule\Repetition;
use Railt\Parser\Rule\Terminal;

/**
 * --- DO NOT EDIT THIS FILE ---
 *
 * Class Parser has been auto-generated.
 * Generated at: 11-07-2018 22:16:05
 *
 * --- DO NOT EDIT THIS FILE ---
 */
class Parser extends Stateful
{
    public const T_PRAGMA = 'T_PRAGMA';
    public const T_INCLUDE = 'T_INCLUDE';
    public const T_TOKEN = 'T_TOKEN';
    public const T_SKIP = 'T_SKIP';
    public const T_OR = 'T_OR';
    public const T_TOKEN_SKIPPED = 'T_TOKEN_SKIPPED';
    public const T_TOKEN_KEPT = 'T_TOKEN_KEPT';
    public const T_TOKEN_STRING = 'T_TOKEN_STRING';
    public const T_INVOKE = 'T_INVOKE';
    public const T_GROUP_OPEN = 'T_GROUP_OPEN';
    public const T_GROUP_CLOSE = 'T_GROUP_CLOSE';
    public const T_REPEAT_ZERO_OR_ONE = 'T_REPEAT_ZERO_OR_ONE';
    public const T_REPEAT_ONE_OR_MORE = 'T_REPEAT_ONE_OR_MORE';
    public const T_REPEAT_ZERO_OR_MORE = 'T_REPEAT_ZERO_OR_MORE';
    public const T_REPEAT_N_TO_M = 'T_REPEAT_N_TO_M';
    public const T_REPEAT_N_OR_MORE = 'T_REPEAT_N_OR_MORE';
    public const T_REPEAT_ZERO_TO_M = 'T_REPEAT_ZERO_TO_M';
    public const T_REPEAT_EXACTLY_N = 'T_REPEAT_EXACTLY_N';
    public const T_KEPT_NAME = 'T_KEPT_NAME';
    public const T_NAME = 'T_NAME';
    public const T_EQ = 'T_EQ';
    public const T_DELEGATE = 'T_DELEGATE';
    public const T_END_OF_RULE = 'T_END_OF_RULE';
    public const T_WHITESPACE = 'T_WHITESPACE';
    public const T_COMMENT = 'T_COMMENT';
    public const T_BLOCK_COMMENT = 'T_BLOCK_COMMENT';

    /**
     * Lexical tokens list.
     *
     * @var string[]
     */
    private const LEXER_TOKENS = [
        self::T_PRAGMA              => '%pragma\\h+([\\w\\.]+)\\h+([^\\s]+)',
        self::T_INCLUDE             => '%include\\h+([^\\s]+)',
        self::T_TOKEN               => '%token\\h+(\\w+)\\h+([^\\s]+)',
        self::T_SKIP                => '%skip\\h+(\\w+)\\h+([^\\s]+)',
        self::T_OR                  => '\\|',
        self::T_TOKEN_SKIPPED       => '::(\\w+)::',
        self::T_TOKEN_KEPT          => '<(\\w+)>',
        self::T_TOKEN_STRING        => '("[^"\\\\]+(\\\\.[^"\\\\]*)*"|\'[^\'\\\\]+(\\\\.[^\'\\\\]*)*\')',
        self::T_INVOKE              => '(\\w+)\\(\\)',
        self::T_GROUP_OPEN          => '\\(',
        self::T_GROUP_CLOSE         => '\\)',
        self::T_REPEAT_ZERO_OR_ONE  => '\\?',
        self::T_REPEAT_ONE_OR_MORE  => '\\+',
        self::T_REPEAT_ZERO_OR_MORE => '\\*',
        self::T_REPEAT_N_TO_M       => '{\\h*(\\-?\\d+)\\h*,\\h*(\\-?\\d+)\\h*}',
        self::T_REPEAT_N_OR_MORE    => '{\\h*(\\-?\\d+)\\h*,\\h*}',
        self::T_REPEAT_ZERO_TO_M    => '{\\h*,\\h*(\\-?\\d+)\\h*}',
        self::T_REPEAT_EXACTLY_N    => '{\\h*(\\-?\\d+)\\h*}',
        self::T_KEPT_NAME           => '#',
        self::T_NAME                => '[a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*',
        self::T_EQ                  => '(\\:|\\:\\:=|=)',
        self::T_DELEGATE            => '\\->',
        self::T_END_OF_RULE         => ';',
        self::T_WHITESPACE          => '(\\xfe\\xff|\\x20|\\x09|\\x0a|\\x0d)+',
        self::T_COMMENT             => '//[^\\n]*',
        self::T_BLOCK_COMMENT       => '/\\*.*?\\*/',
    ];

    /**
     * List of skipped tokens.
     *
     * @var string[]
     */
    private const LEXER_SKIPPED_TOKENS = [
        'T_WHITESPACE',
        'T_COMMENT',
        'T_BLOCK_COMMENT',
    ];

    /**
     * @var int
     */
    private const LEXER_FLAGS = Factory::LOOKAHEAD;

    /**
     * List of rule delegates.
     *
     * @var string[]
     */
    private const PARSER_DELEGATES = [
        'TokenDefinition'   => \Railt\Compiler\Grammar\Delegate\TokenDelegate::class,
        'IncludeDefinition' => \Railt\Compiler\Grammar\Delegate\IncludeDelegate::class,
        'RuleDefinition'    => \Railt\Compiler\Grammar\Delegate\RuleDelegate::class,
    ];

    /**
     * Parser root rule name.
     *
     * @var string
     */
    private const PARSER_ROOT_RULE = 'Grammar';

    /**
     * @return ParserInterface
     * @throws \InvalidArgumentException
     * @throws \Railt\Lexer\Exception\BadLexemeException
     */
    protected function boot(): ParserInterface
    {
        return new Llk($this->bootLexer(), $this->bootGrammar());
    }

    /**
     * @return LexerInterface
     * @throws \InvalidArgumentException
     * @throws \Railt\Lexer\Exception\BadLexemeException
     */
    private function bootLexer(): LexerInterface
    {
        return Factory::create(self::LEXER_TOKENS, self::LEXER_SKIPPED_TOKENS, self::LEXER_FLAGS);
    }

    /**
     * @return GrammarInterface
     */
    private function bootGrammar(): GrammarInterface
    {
        return new Grammar([
            new Repetition(0, 0, -1, '__definition', null),
            (new Concatenation('Grammar', [0], 'Grammar'))->setDefaultId('Grammar'),
            new Concatenation(2, ['RuleDefinition'], null),
            new Alternation('__definition', ['TokenDefinition', 'PragmaDefinition', 'IncludeDefinition', 2], null),
            new Terminal(4, 'T_TOKEN', true),
            new Concatenation(5, [4], 'TokenDefinition'),
            new Terminal(6, 'T_SKIP', true),
            new Concatenation(7, [6], 'TokenDefinition'),
            (new Alternation('TokenDefinition', [5, 7], null))->setDefaultId('TokenDefinition'),
            new Terminal(9, 'T_PRAGMA', true),
            (new Concatenation('PragmaDefinition', [9], 'PragmaDefinition'))->setDefaultId('PragmaDefinition'),
            new Terminal(11, 'T_INCLUDE', true),
            (new Concatenation('IncludeDefinition', [11], 'IncludeDefinition'))->setDefaultId('IncludeDefinition'),
            new Repetition(13, 0, 1, 'ShouldKeep', null),
            new Repetition(14, 0, 1, 'RuleDelegate', null),
            new Terminal(15, 'T_EQ', false),
            new Terminal(16, 'T_END_OF_RULE', false),
            new Repetition(17, 0, 1, 16, null),
            (new Concatenation('RuleDefinition', [13, 'RuleName', 14, 15, 'RuleProduction', 17], 'RuleDefinition'))->setDefaultId('RuleDefinition'),
            new Terminal(19, 'T_NAME', true),
            (new Concatenation('RuleName', [19], 'RuleName'))->setDefaultId('RuleName'),
            new Terminal(21, 'T_DELEGATE', false),
            new Terminal(22, 'T_NAME', true),
            (new Concatenation('RuleDelegate', [21, 22], 'RuleDelegate'))->setDefaultId('RuleDelegate'),
            new Terminal(24, 'T_KEPT_NAME', false),
            (new Concatenation('ShouldKeep', [24], 'ShouldKeep'))->setDefaultId('ShouldKeep'),
            new Concatenation(26, ['__alternation'], null),
            (new Concatenation('RuleProduction', [26], 'RuleProduction'))->setDefaultId('RuleProduction'),
            new Concatenation(28, ['Alternation'], null),
            new Alternation('__alternation', ['__concatenation', 28], null),
            new Terminal(30, 'T_OR', true),
            new Concatenation(31, [30, '__concatenation'], 'Alternation'),
            new Repetition(32, 1, -1, 31, null),
            (new Concatenation('Alternation', ['__concatenation', 32], null))->setDefaultId('Alternation'),
            new Concatenation(34, ['Concatenation'], null),
            new Alternation('__concatenation', ['__repetition', 34], null),
            new Repetition(36, 1, -1, '__repetition', null),
            (new Concatenation('Concatenation', ['__repetition', 36], 'Concatenation'))->setDefaultId('Concatenation'),
            new Alternation(38, ['__simple', 'Repetition'], null),
            new Repetition(39, 0, 1, 'Rename', null),
            new Concatenation('__repetition', [38, 39], null),
            new Concatenation(41, ['Quantifier'], null),
            (new Concatenation('Repetition', ['__simple', 41], 'Repetition'))->setDefaultId('Repetition'),
            new Terminal(43, 'T_GROUP_OPEN', true),
            new Terminal(44, 'T_GROUP_CLOSE', true),
            new Concatenation(45, [43, '__alternation', 44], null),
            new Terminal(46, 'T_TOKEN_SKIPPED', true),
            new Terminal(47, 'T_TOKEN_KEPT', true),
            new Terminal(48, 'T_TOKEN_STRING', true),
            new Terminal(49, 'T_INVOKE', true),
            new Alternation('__simple', [45, 46, 47, 48, 49], null),
            new Terminal(51, 'T_REPEAT_ZERO_OR_ONE', true),
            new Concatenation(52, [51], 'Quantifier'),
            new Terminal(53, 'T_REPEAT_ONE_OR_MORE', true),
            new Concatenation(54, [53], 'Quantifier'),
            new Terminal(55, 'T_REPEAT_ZERO_OR_MORE', true),
            new Concatenation(56, [55], 'Quantifier'),
            new Terminal(57, 'T_REPEAT_N_TO_M', true),
            new Concatenation(58, [57], 'Quantifier'),
            new Terminal(59, 'T_REPEAT_ZERO_OR_MORE', true),
            new Concatenation(60, [59], 'Quantifier'),
            new Terminal(61, 'T_REPEAT_ZERO_TO_M', true),
            new Concatenation(62, [61], 'Quantifier'),
            new Terminal(63, 'T_REPEAT_N_OR_MORE', true),
            new Concatenation(64, [63], 'Quantifier'),
            new Terminal(65, 'T_REPEAT_EXACTLY_N', true),
            new Concatenation(66, [65], 'Quantifier'),
            (new Alternation('Quantifier', [52, 54, 56, 58, 60, 62, 64, 66], null))->setDefaultId('Quantifier'),
            new Terminal(68, 'T_KEPT_NAME', true),
            new Terminal(69, 'T_NAME', true),
            (new Concatenation('Rename', [68, 69], 'Rename'))->setDefaultId('Rename'),
        ], self::PARSER_ROOT_RULE, self::PARSER_DELEGATES);
    }
}
