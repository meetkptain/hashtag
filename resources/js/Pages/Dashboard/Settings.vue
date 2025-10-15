<template>
  <Layout title="Paramètres du Widget">
    <div class="max-w-4xl">
      <form @submit.prevent="saveSettings" class="space-y-8">
        <!-- Appearance Section -->
        <div class="card">
          <h3 class="text-lg font-semibold mb-6">Apparence</h3>
          
          <div class="space-y-4">
            <div>
              <label class="label">Thème</label>
              <select v-model="settings.theme" class="input">
                <option value="light">Clair</option>
                <option value="dark">Sombre</option>
                <option value="custom">Personnalisé</option>
              </select>
            </div>

            <div>
              <label class="label">Direction du défilement</label>
              <select v-model="settings.direction" class="input">
                <option value="vertical">Vertical</option>
                <option value="horizontal">Horizontal</option>
              </select>
            </div>

            <div>
              <label class="label">Vitesse de défilement</label>
              <select v-model="settings.speed" class="input">
                <option value="slow">Lent</option>
                <option value="medium">Moyen</option>
                <option value="fast">Rapide</option>
              </select>
            </div>

            <div>
              <label class="label">Posts par vue</label>
              <input v-model.number="settings.posts_per_view" type="number" min="1" max="10" class="input">
            </div>
          </div>
        </div>

        <!-- Behavior Section -->
        <div class="card">
          <h3 class="text-lg font-semibold mb-6">Comportement</h3>
          
          <div class="space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="settings.autoplay" type="checkbox" class="w-5 h-5 text-primary-600 rounded">
              <div>
                <p class="font-medium">Lecture automatique</p>
                <p class="text-sm text-gray-500">Défile automatiquement les posts</p>
              </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="settings.fullscreen_enabled" type="checkbox" class="w-5 h-5 text-primary-600 rounded">
              <div>
                <p class="font-medium">Mode plein écran</p>
                <p class="text-sm text-gray-500">Afficher le bouton plein écran</p>
              </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="settings.gamification_enabled" type="checkbox" class="w-5 h-5 text-primary-600 rounded">
              <div>
                <p class="font-medium">Gamification</p>
                <p class="text-sm text-gray-500">Badges, surbrillance, animations ludiques</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Custom Colors (if theme is custom) -->
        <div v-if="settings.theme === 'custom'" class="card">
          <h3 class="text-lg font-semibold mb-6">Couleurs personnalisées</h3>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Couleur primaire</label>
              <input v-model="settings.colors.primary" type="color" class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
            </div>

            <div>
              <label class="label">Couleur secondaire</label>
              <input v-model="settings.colors.secondary" type="color" class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
            </div>

            <div>
              <label class="label">Arrière-plan</label>
              <input v-model="settings.colors.background" type="color" class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
            </div>

            <div>
              <label class="label">Texte</label>
              <input v-model="settings.colors.text" type="color" class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
            </div>
          </div>
        </div>

        <!-- Widget Code -->
        <div class="card bg-gradient-to-r from-primary-500 to-purple-600 text-white">
          <h3 class="text-lg font-semibold mb-4">Code d'intégration</h3>
          <p class="text-sm mb-4 opacity-90">Copiez ce code et collez-le sur votre site web</p>
          
          <div class="bg-black/20 rounded-lg p-4">
            <code class="text-sm block overflow-x-auto whitespace-pre">{{ widgetCode }}</code>
          </div>
          
          <button type="button" @click="copyCode" class="mt-4 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
            📋 Copier le code
          </button>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3">
          <button type="button" class="btn btn-secondary">
            Réinitialiser
          </button>
          <button type="submit" class="btn btn-primary">
            💾 Enregistrer les paramètres
          </button>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Layout from '../../Components/Layout.vue';

const page = usePage();

const settings = ref({
  theme: 'light',
  direction: 'vertical',
  speed: 'medium',
  autoplay: true,
  fullscreen_enabled: true,
  gamification_enabled: false,
  posts_per_view: 3,
  colors: {
    primary: '#3b82f6',
    secondary: '#8b5cf6',
    background: '#ffffff',
    text: '#1f2937',
  }
});

const widgetCode = computed(() => {
  const apiKey = page.props.tenant?.api_key || 'YOUR_API_KEY';
  const domain = window.location.origin;
  
  return `<div id="hashmytag-wall"></div>
<script src="${domain}/widget.js"><\/script>
<script>
  HashMyTag.init({
    apiKey: '${apiKey}',
    theme: '${settings.value.theme}',
    direction: '${settings.value.direction}',
    speed: '${settings.value.speed}',
    gamification: ${settings.value.gamification_enabled},
    fullscreen: ${settings.value.fullscreen_enabled}
  });
<\/script>`;
});

onMounted(async () => {
  try {
    const response = await axios.get('/api/settings');
    if (response.data.settings) {
      settings.value = { ...settings.value, ...response.data.settings };
    }
  } catch (error) {
    console.error('Failed to load settings', error);
  }
});

const saveSettings = async () => {
  try {
    await axios.put('/api/settings', settings.value);
    alert('Paramètres enregistrés avec succès !');
  } catch (error) {
    alert('Erreur lors de l\'enregistrement des paramètres');
    console.error(error);
  }
};

const copyCode = () => {
  navigator.clipboard.writeText(widgetCode.value);
  alert('Code copié dans le presse-papiers !');
};
</script>

