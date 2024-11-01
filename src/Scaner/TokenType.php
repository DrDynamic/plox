<?php

namespace src\Scaner;

enum TokenType: string
{
// Single-character tokens.
    case LEFT_PAREN = "LEFT_PAREN";
    case RIGHT_PAREN = "RIGHT_PAREN";
    case LEFT_BRACE = "LEFT_BRACE";
    case RIGHT_BRACE = "RIGHT_BRACE";
    case COMMA = "COMMA";
    case DOT = "DOT";
    case COLON = "COLON";
    case QUESTION_MARK = "QUESTION_MARK";
    case MINUS = "MINUS";
    case PLUS = "PLUS";
    case SEMICOLON = "SEMICOLON";
    case SLASH = "SLASH";
    case STAR = "STAR";

    // One or two character tokens.
    case BANG = "BANG";
    case BANG_EQUAL = "BANG_EQUAL";
    case EQUAL = "EQUAL";
    case EQUAL_EQUAL = "EQUAL_EQUAL";
    case GREATER = "GREATER";
    case GREATER_EQUAL = "GREATER_EQUAL";
    case LESS = "LESS";
    case LESS_EQUAL = "LESS_EQUAL";

    // Literals.
    case IDENTIFIER = "IDENTIFIER";
    case STRING = "STRING";
    case NUMBER = "NUMBER";

    // Keywords.
    case AND = "AND";
    case CLS = "CLS";
    case ELSE = "ELSE";
    case FALSE = "FALSE";
    case FUNCTION = "FUNCTION";
    case FOR = "FOR";
    case IF = "IF";
    case NIL = "NIL";
    case OR = "OR";
    case RETURN = "RETURN";
    case SUPER = "SUPER";
    case THIS = "THIS";
    case TRUE = "TRUE";
    case VAR = "VAR";
    case WHILE = "WHILE";
    case BREAK = "BREAK";
    case CONTINUE = "CONTINUE";
    case PUBLIC = "PUBLIC";
    case PRIVATE = "PRIVATE";
    case STATIC = "STATIC";

    case LINE_BREAK = "LINE_BREAK";
    case EOF = "EOF";
    case ERROR = "ERROR";
}
