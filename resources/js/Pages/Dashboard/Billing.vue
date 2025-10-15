<template>
  <Layout title="Facturation">
    <!-- Current Plan -->
    <div class="card mb-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-2xl font-bold text-gray-900">Plan {{ currentPlan.name }}</h3>
          <p class="text-gray-600 mt-1">{{ currentPlan.description }}</p>
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold text-primary-600">{{ currentPlan.price_monthly }}€</p>
          <p class="text-sm text-gray-500">/mois</p>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="text-center p-4 rounded-lg bg-gray-50">
          <p class="text-2xl font-bold text-gray-900">{{ features.feeds === -1 ? '∞' : features.feeds }}</p>
          <p class="text-sm text-gray-600 mt-1">Flux</p>
        </div>
        <div class="text-center p-4 rounded-lg bg-gray-50">
          <p class="text-2xl font-bold text-gray-900">{{ features.hashtags === -1 ? '∞' : features.hashtags }}</p>
          <p class="text-sm text-gray-600 mt-1">Hashtags</p>
        </div>
        <div class="text-center p-4 rounded-lg bg-gray-50">
          <p class="text-2xl font-bold text-gray-900">{{ features.posts_limit === -1 ? '∞' : features.posts_limit }}</p>
          <p class="text-sm text-gray-600 mt-1">Posts</p>
        </div>
        <div class="text-center p-4 rounded-lg bg-gray-50">
          <p class="text-2xl font-bold text-gray-900">{{ features.gamification ? '✓' : '✗' }}</p>
          <p class="text-sm text-gray-600 mt-1">Gamification</p>
        </div>
      </div>

      <div class="flex gap-3">
        <button @click="manageBilling" class="btn btn-primary">
          Gérer l'abonnement
        </button>
        <button class="btn btn-secondary">
          Voir les factures
        </button>
      </div>
    </div>

    <!-- Available Plans -->
    <h3 class="text-xl font-semibold mb-6">Changer de plan</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div 
        v-for="(plan, key) in plans" 
        :key="key"
        :class="[
          'card',
          plan.popular ? 'ring-2 ring-primary-500 relative' : ''
        ]"
      >
        <div v-if="plan.popular" class="absolute top-0 right-0 bg-primary-500 text-white px-3 py-1 rounded-bl-lg rounded-tr-lg text-xs font-semibold">
          Populaire
        </div>

        <h4 class="text-xl font-bold text-gray-900 mb-2">{{ plan.name }}</h4>
        <p class="text-gray-600 text-sm mb-4">{{ plan.description }}</p>

        <div class="mb-6">
          <p class="text-4xl font-bold text-gray-900">{{ plan.price_monthly }}€</p>
          <p class="text-sm text-gray-500">/mois</p>
        </div>

        <ul class="space-y-3 mb-6">
          <li class="flex items-start gap-2 text-sm">
            <span class="text-green-500 mt-0.5">✓</span>
            <span>{{ plan.features.feeds === -1 ? 'Flux illimités' : `${plan.features.feeds} flux` }}</span>
          </li>
          <li class="flex items-start gap-2 text-sm">
            <span class="text-green-500 mt-0.5">✓</span>
            <span>{{ plan.features.hashtags === -1 ? 'Hashtags illimités' : `${plan.features.hashtags} hashtags` }}</span>
          </li>
          <li class="flex items-start gap-2 text-sm">
            <span class="text-green-500 mt-0.5">✓</span>
            <span>Analytics {{ plan.features.analytics }}</span>
          </li>
          <li class="flex items-start gap-2 text-sm">
            <span :class="plan.features.gamification ? 'text-green-500' : 'text-gray-300'">{{ plan.features.gamification ? '✓' : '✗' }}</span>
            <span>Gamification</span>
          </li>
          <li class="flex items-start gap-2 text-sm">
            <span :class="plan.features.custom_branding ? 'text-green-500' : 'text-gray-300'">{{ plan.features.custom_branding ? '✓' : '✗' }}</span>
            <span>Branding personnalisé</span>
          </li>
        </ul>

        <button 
          @click="changePlan(key)" 
          :class="currentPlanKey === key ? 'btn-secondary' : 'btn-primary'" 
          class="btn w-full"
          :disabled="currentPlanKey === key"
        >
          {{ currentPlanKey === key ? 'Plan actuel' : 'Choisir ce plan' }}
        </button>
      </div>
    </div>

    <!-- Add-ons -->
    <div class="card mt-8">
      <h3 class="text-xl font-semibold mb-6">Extensions disponibles</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-for="(addon, key) in addons" :key="key" class="border border-gray-200 rounded-lg p-4">
          <h4 class="font-semibold text-gray-900 mb-2">{{ addon.name }}</h4>
          <p class="text-2xl font-bold text-primary-600 mb-4">{{ addon.price }}€/mois</p>
          <button class="btn btn-secondary w-full text-sm">
            Ajouter
          </button>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Layout from '../../Components/Layout.vue';

const page = usePage();

const plans = ref({
  starter: {
    name: 'Starter',
    description: 'Parfait pour commencer',
    price_monthly: 29,
    price_yearly: 290,
    features: {
      feeds: 1,
      hashtags: 3,
      posts_limit: 50,
      gamification: false,
      custom_branding: false,
      analytics: 'basic',
      support: 'email',
    }
  },
  business: {
    name: 'Business',
    description: 'Pour les entreprises en croissance',
    price_monthly: 79,
    price_yearly: 790,
    popular: true,
    features: {
      feeds: 3,
      hashtags: 10,
      posts_limit: 200,
      gamification: true,
      custom_branding: true,
      analytics: 'advanced',
      support: 'priority',
    }
  },
  enterprise: {
    name: 'Enterprise',
    description: 'Solution complète',
    price_monthly: 199,
    price_yearly: 1990,
    features: {
      feeds: -1,
      hashtags: -1,
      posts_limit: -1,
      gamification: true,
      custom_branding: true,
      analytics: 'premium',
      support: 'dedicated',
      api_access: true,
      white_label: true,
    }
  }
});

const addons = ref({
  extra_feed: {
    name: 'Flux supplémentaire',
    price: 15
  },
  extra_hashtags: {
    name: '5 hashtags supplémentaires',
    price: 10
  },
  premium_support: {
    name: 'Support prioritaire',
    price: 50
  }
});

const currentPlanKey = computed(() => page.props.tenant?.plan || 'starter');
const currentPlan = computed(() => plans.value[currentPlanKey.value] || plans.value.starter);
const features = computed(() => currentPlan.value.features);

const changePlan = async (planKey) => {
  try {
    const response = await axios.post('/stripe/checkout', {
      plan: planKey,
      interval: 'monthly'
    });

    if (response.data.url) {
      window.location.href = response.data.url;
    }
  } catch (error) {
    alert('Erreur lors du changement de plan');
    console.error(error);
  }
};

const manageBilling = async () => {
  try {
    const response = await axios.post('/stripe/portal');
    
    if (response.data.url) {
      window.location.href = response.data.url;
    }
  } catch (error) {
    alert('Erreur lors de l\'accès au portail de facturation');
    console.error(error);
  }
};
</script>

