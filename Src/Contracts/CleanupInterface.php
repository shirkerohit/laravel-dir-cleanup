<?php

namespace Rohits\Src\Contracts;

interface CleanupInterface
{
    public function cleanup();
    public function getRoot();
    public function getDirectories();
    public function getDepth();
    public function getFileExtentionPattern();
}
