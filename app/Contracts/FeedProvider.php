<?php

namespace App\Contracts;

interface FeedProvider
{
    /**
     * Récupérer les posts depuis la source
     *
     * @param array $config Configuration du feed (hashtags, etc.)
     * @return array
     */
    public function fetch(array $config): array;

    /**
     * Normaliser les données pour le format unifié
     *
     * @param mixed $data
     * @return array
     */
    public function normalize($data): array;

    /**
     * Valider la configuration du feed
     *
     * @param array $config
     * @return bool
     */
    public function validateConfig(array $config): bool;

    /**
     * Obtenir le nom de la plateforme
     *
     * @return string
     */
    public function getPlatformName(): string;
}

