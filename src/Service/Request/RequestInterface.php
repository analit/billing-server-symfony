<?php


namespace App\Service\Request;


interface RequestInterface
{
    public function getName(): string;

    public function getId(): string;
}