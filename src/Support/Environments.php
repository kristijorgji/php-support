<?php declare(strict_types = 1);

namespace kristijorgji\Support;

enum Environments: string
{
    case LOCAL = 'local';
    case DEVELOPMENT = 'development';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case TESTING = 'testing';
}
