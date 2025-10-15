<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-6">Mode de Connexion</h2>
      
      <div class="space-y-4">
        <!-- Mode Simple -->
        <label 
          :class="[
            'border-2 rounded-lg p-4 cursor-pointer transition',
            connectionMode === 'simple' 
              ? 'border-primary-500 bg-primary-50' 
              : 'border-gray-300 hover:border-gray-400'
          ]"
        >
          <div class="flex items-start gap-3">
            <input 
              type="radio" 
              v-model="connectionMode" 
              value="simple"
              class="mt-1"
            >
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-lg">Mode Simple</span>
                <span class="badge badge-success">Inclus</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">
                Affiche les posts publics avec vos hashtags. Utilise l'API HashMyTag.
              </p>
              <ul class="text-sm text-gray-500 mt-2 space-y-1">
                <li>✅ Configuration en 2 minutes</li>
                <li>✅ Pas de compte développeur nécessaire</li>
                <li>✅ Posts publics avec hashtags</li>
                <li>⚠️ Limites API partagées</li>
              </ul>
            </div>
          </div>
        </label>
        
        <!-- Mode Avancé -->
        <label 
          :class="[
            'border-2 rounded-lg p-4 cursor-pointer transition',
            connectionMode === 'advanced' 
              ? 'border-purple-500 bg-purple-50' 
              : 'border-gray-300 hover:border-gray-400'
          ]"
        >
          <div class="flex items-start gap-3">
            <input 
              type="radio" 
              v-model="connectionMode" 
              value="advanced"
              class="mt-1"
              :disabled="!canUseAdvanced"
            >
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-lg">Mode Avancé</span>
                <span class="badge badge-warning">+20€/mois</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">
                Connectez votre compte pour un accès complet. Limites API dédiées.
              </p>
              <ul class="text-sm text-gray-500 mt-2 space-y-1">
                <li>✅ Tous vos posts (même privés)</li>
                <li>✅ Stories, mentions, tags</li>
                <li>✅ Limites API dédiées (200/h)</li>
                <li>✅ Aucune limite partagée</li>
              </ul>
              <p v-if="!canUseAdvanced" class="text-xs text-red-600 mt-2">
                Nécessite plan Business ou Enterprise
              </p>
            </div>
          </div>
        </label>
      </div>
      
      <!-- Actions selon le mode -->
      <div class="mt-6">
        <div v-if="connectionMode === 'simple'">
          <button @click="saveSimpleMode" class="btn btn-primary w-full">
            Continuer avec Mode Simple
          </button>
        </div>
        
        <div v-if="connectionMode === 'advanced'">
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-yellow-800">
              <strong>Note :</strong> Le mode avancé coûte 20€/mois supplémentaires par compte connecté.
            </p>
          </div>
          
          <button 
            @click="confirmAdvanced" 
            class="btn btn-primary w-full"
            :disabled="!canUseAdvanced"
          >
            <span v-if="canUseAdvanced">
              Continuer avec Mode Avancé (+20€/mois)
            </span>
            <span v-else>
              Upgrade requis pour Mode Avancé
            </span>
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from './Modal.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  feed: Object,
  tenant: Object,
});

const emit = defineEmits(['close', 'modeSelected']);

const page = usePage();
const connectionMode = ref('simple');

// Vérifier si le client peut utiliser le mode avancé
const canUseAdvanced = computed(() => {
  const plan = page.props.tenant?.plan || 'starter';
  // Starter ne peut pas, Business+Enterprise peuvent
  return ['business', 'enterprise'].includes(plan);
});

const saveSimpleMode = () => {
  emit('modeSelected', { mode: 'simple', credentials: null });
};

const confirmAdvanced = () => {
  if (!canUseAdvanced.value) {
    alert('Le mode avancé nécessite un plan Business ou Enterprise');
    return;
  }
  emit('modeSelected', { mode: 'advanced', credentials: null });
};
</script>

