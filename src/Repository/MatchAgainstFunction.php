<?php

namespace App\Repository;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * @example by https://gist.github.com/1234419 Jérémy Hubert
 * "MATCH_AGAINST" "(" {StateFieldPathExpression ","}* InParameter {Literal}? ")"
 */
class MatchAgainstFunction extends FunctionNode
{
    /** @var array<int, \Doctrine\ORM\Query\AST\PathExpression> */
    public array $columns = [];
    public \Doctrine\ORM\Query\AST\Node|string|null $needle = null;
    public \Doctrine\ORM\Query\AST\Literal|null $mode = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        do {
            $this->columns[] = $parser->StateFieldPathExpression();

            if ($parser->getLexer()->lookahead?->type !== TokenType::T_COMMA) {
                break;
            }

            $parser->match(TokenType::T_COMMA);

            if ($parser->getLexer()->lookahead?->type !== TokenType::T_IDENTIFIER) {
                break;
            }
        } while (true);

        $this->needle = $parser->InParameter();

        if ($parser->getLexer()->lookahead?->type === TokenType::T_STRING) {
            $this->mode = $parser->Literal();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $haystack = null;
        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }
        $query = 'MATCH('.$haystack.
            ') AGAINST ('.$this->needle->dispatch($sqlWalker);
        if ($this->mode) {
            $query .= ' '.$this->mode->dispatch($sqlWalker).' )';
        } else {
            $query .= ' )';
        }
        return $query;
    }
}
