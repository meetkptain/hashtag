<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 via-purple-50 to-pink-50">
    <!-- Navigation -->
    <nav class="container mx-auto px-6 py-8 flex items-center justify-between">
      <div class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-purple-600 bg-clip-text text-transparent">
        HashMyTag
      </div>
      <div class="flex gap-4">
        <Link href="/login" class="btn btn-secondary">
          Connexion
        </Link>
        <Link href="/register" class="btn btn-primary">
          Essai gratuit
        </Link>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="container mx-auto px-6 py-20 text-center">
      <h1 class="text-6xl font-bold text-gray-900 mb-6">
        Transformez vos réseaux sociaux<br>
        en <span class="bg-gradient-to-r from-primary-600 to-purple-600 bg-clip-text text-transparent">mur social live</span>
      </h1>
      <p class="text-xl text-gray-600 mb-12 max-w-3xl mx-auto">
        Affichez en temps réel vos posts Instagram, Facebook, Twitter et avis Google.<br>
        Widget responsive, gamification intégrée, personnalisation complète.
      </p>
      
      <div class="flex justify-center gap-4 mb-16">
        <Link href="/register" class="btn btn-primary text-lg px-8 py-4">
          🚀 Commencer gratuitement
        </Link>
        <a href="#demo" class="btn btn-secondary text-lg px-8 py-4">
          👀 Voir la démo
        </a>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-20">
        <div class="card text-left">
          <div class="text-4xl mb-4">📱</div>
          <h3 class="text-xl font-bold mb-2">Multi-plateformes</h3>
          <p class="text-gray-600">Instagram, Facebook, Twitter/X, Google Reviews. Architecture extensible pour ajouter facilement de nouveaux flux.</p>
        </div>

        <div class="card text-left">
          <div class="text-4xl mb-4">🎮</div>
          <h3 class="text-xl font-bold mb-2">Gamification</h3>
          <p class="text-gray-600">Badges, surbrillance, animations ludiques pour engager votre audience et valoriser vos meilleurs contenus.</p>
        </div>

        <div class="card text-left">
          <div class="text-4xl mb-4">🎨</div>
          <h3 class="text-xl font-bold mb-2">100% Personnalisable</h3>
          <p class="text-gray-600">Thèmes, couleurs, vitesse, direction. Adaptez le widget à votre identité visuelle en quelques clics.</p>
        </div>

        <div class="card text-left">
          <div class="text-4xl mb-4">⚡</div>
          <h3 class="text-xl font-bold mb-2">Ultra Rapide</h3>
          <h3 class="text-gray-600">Chargement asynchrone, cache intelligent, défilement fluide. Performance optimale sur tous les appareils.</p>
        </div>

        <div class="card text-left">
          <div class="text-4xl mb-4">📊</div>
          <h3 class="text-xl font-bold mb-2">Analytics Complets</h3>
          <p class="text-gray-600">Suivez les vues, clics, engagement. Statistiques en temps réel par plateforme et par post.</p>
        </div>

        <div class="card text-left">
          <div class="text-4xl mb-4">💳</div>
          <h3 class="text-xl font-bold mb-2">Facturation Simple</h3>
          <p class="text-gray-600">Paiements sécurisés Stripe, abonnements mensuels/annuels, essai gratuit 14 jours.</p>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="container mx-auto px-6 py-20" id="pricing">
      <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">
        Tarifs transparents
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <div class="card">
          <h3 class="text-2xl font-bold mb-2">Starter</h3>
          <p class="text-gray-600 mb-4">Parfait pour commencer</p>
          <div class="mb-6">
            <span class="text-5xl font-bold">29€</span>
            <span class="text-gray-600">/mois</span>
          </div>
          <ul class="space-y-3 mb-6">
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>1 flux</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>3 hashtags</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>50 posts max</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Analytics basiques</span>
            </li>
          </ul>
          <Link href="/register" class="btn btn-secondary w-full">
            Essayer gratuitement
          </Link>
        </div>

        <div class="card ring-2 ring-primary-500 relative">
          <div class="absolute top-0 right-0 bg-primary-500 text-white px-3 py-1 rounded-bl-lg rounded-tr-lg text-xs font-semibold">
            Populaire
          </div>
          <h3 class="text-2xl font-bold mb-2">Business</h3>
          <p class="text-gray-600 mb-4">Pour entreprises</p>
          <div class="mb-6">
            <span class="text-5xl font-bold">79€</span>
            <span class="text-gray-600">/mois</span>
          </div>
          <ul class="space-y-3 mb-6">
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>3 flux</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>10 hashtags</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>200 posts max</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Gamification</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Analytics avancés</span>
            </li>
          </ul>
          <Link href="/register" class="btn btn-primary w-full">
            Essayer gratuitement
          </Link>
        </div>

        <div class="card">
          <h3 class="text-2xl font-bold mb-2">Enterprise</h3>
          <p class="text-gray-600 mb-4">Solution complète</p>
          <div class="mb-6">
            <span class="text-5xl font-bold">199€</span>
            <span class="text-gray-600">/mois</span>
          </div>
          <ul class="space-y-3 mb-6">
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Flux illimités</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Hashtags illimités</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Posts illimités</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>White label</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-500">✓</span>
              <span>Support dédié</span>
            </li>
          </ul>
          <Link href="/register" class="btn btn-secondary w-full">
            Essayer gratuitement
          </Link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="container mx-auto px-6 text-center">
        <div class="text-2xl font-bold mb-4 bg-gradient-to-r from-primary-400 to-purple-400 bg-clip-text text-transparent">
          HashMyTag
        </div>
        <p class="text-gray-400 mb-4">
          Transformez votre présence sociale en expérience interactive
        </p>
        <p class="text-gray-500 text-sm">
          © 2025 HashMyTag. Tous droits réservés.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

