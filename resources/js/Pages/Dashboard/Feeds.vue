<template>
  <Layout title="Gestion des Flux">
    <template #header-actions>
      <button @click="showAddModal = true" class="btn btn-primary">
        ➕ Ajouter un flux
      </button>
    </template>

    <!-- Feeds Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="feed in feeds" :key="feed.id" class="card hover:shadow-lg transition-shadow">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <span class="text-3xl">{{ getPlatformIcon(feed.type) }}</span>
            <div>
              <h3 class="font-semibold text-gray-900">{{ feed.name }}</h3>
              <p class="text-sm text-gray-500">{{ getPlatformName(feed.type) }}</p>
            </div>
          </div>
          <span :class="feed.active ? 'badge-success' : 'badge-danger'" class="badge">
            {{ feed.active ? 'Actif' : 'Inactif' }}
          </span>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Posts</span>
            <span class="font-medium">{{ feed.posts_count }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Hashtags</span>
            <span class="font-medium">{{ feed.config.hashtags?.length || 0 }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Dernière synchro</span>
            <span class="font-medium">{{ formatTime(feed.last_fetched_at) }}</span>
          </div>
        </div>

        <div class="flex gap-2">
          <button @click="syncFeed(feed)" class="btn btn-secondary flex-1 text-sm">
            🔄 Synchroniser
          </button>
          <button @click="editFeed(feed)" class="btn btn-secondary flex-1 text-sm">
            ✏️ Modifier
          </button>
          <button @click="deleteFeed(feed)" class="btn btn-danger text-sm">
            🗑️
          </button>
        </div>
      </div>

      <!-- Add Feed Card -->
      <button @click="showAddModal = true" class="card border-2 border-dashed border-gray-300 hover:border-primary-500 hover:bg-primary-50 flex items-center justify-center min-h-[250px] transition-colors">
        <div class="text-center">
          <span class="text-5xl mb-3 block">➕</span>
          <p class="font-medium text-gray-900">Ajouter un flux</p>
          <p class="text-sm text-gray-500 mt-1">Instagram, Facebook, Twitter, Google Reviews</p>
        </div>
      </button>
    </div>

    <!-- Add/Edit Feed Modal -->
    <Modal :show="showAddModal" @close="showAddModal = false">
      <div class="p-6">
        <h2 class="text-2xl font-bold mb-6">{{ editingFeed ? 'Modifier le flux' : 'Ajouter un flux' }}</h2>
        
        <form @submit.prevent="saveFeed" class="space-y-4">
          <div>
            <label class="label">Nom du flux</label>
            <input v-model="form.name" type="text" class="input" placeholder="Mon flux Instagram" required>
          </div>

          <div>
            <label class="label">Type de plateforme</label>
            <select v-model="form.type" class="input" required>
              <option value="instagram">Instagram</option>
              <option value="facebook">Facebook</option>
              <option value="twitter">Twitter / X</option>
              <option value="google_reviews">Google Reviews</option>
            </select>
          </div>

          <div v-if="form.type !== 'google_reviews'">
            <label class="label">Hashtags (un par ligne)</label>
            <textarea v-model="hashtagsText" class="input" rows="4" placeholder="#hashtag1&#10;#hashtag2"></textarea>
          </div>

          <div v-if="form.type === 'facebook'">
            <label class="label">Page ID Facebook</label>
            <input v-model="form.config.page_id" type="text" class="input" placeholder="123456789">
          </div>

          <div v-if="form.type === 'google_reviews'">
            <label class="label">Place ID Google</label>
            <input v-model="form.config.place_id" type="text" class="input" placeholder="ChIJ...">
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="showAddModal = false" class="btn btn-secondary flex-1">
              Annuler
            </button>
            <button type="submit" class="btn btn-primary flex-1">
              {{ editingFeed ? 'Mettre à jour' : 'Ajouter' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Layout from '../../Components/Layout.vue';
import Modal from '../../Components/Modal.vue';

const feeds = ref([]);
const showAddModal = ref(false);
const editingFeed = ref(null);

const form = ref({
  name: '',
  type: 'instagram',
  config: {
    hashtags: [],
    page_id: '',
    place_id: '',
  }
});

const hashtagsText = computed({
  get: () => form.value.config.hashtags?.join('\n') || '',
  set: (value) => {
    form.value.config.hashtags = value.split('\n').map(h => h.trim()).filter(h => h);
  }
});

onMounted(() => {
  loadFeeds();
});

const loadFeeds = async () => {
  try {
    const response = await axios.get('/api/feeds');
    feeds.value = response.data.feeds;
  } catch (error) {
    console.error('Failed to load feeds', error);
  }
};

const saveFeed = async () => {
  try {
    if (editingFeed.value) {
      await axios.put(`/api/feeds/${editingFeed.value.id}`, form.value);
    } else {
      await axios.post('/api/feeds', form.value);
    }
    
    showAddModal.value = false;
    editingFeed.value = null;
    resetForm();
    loadFeeds();
  } catch (error) {
    alert('Erreur lors de la sauvegarde du flux');
    console.error(error);
  }
};

const editFeed = (feed) => {
  editingFeed.value = feed;
  form.value = {
    name: feed.name,
    type: feed.type,
    config: { ...feed.config }
  };
  showAddModal.value = true;
};

const deleteFeed = async (feed) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce flux ?')) return;
  
  try {
    await axios.delete(`/api/feeds/${feed.id}`);
    loadFeeds();
  } catch (error) {
    alert('Erreur lors de la suppression du flux');
  }
};

const syncFeed = async (feed) => {
  try {
    await axios.post(`/api/feeds/${feed.id}/sync`);
    alert('Synchronisation lancée !');
    setTimeout(loadFeeds, 2000);
  } catch (error) {
    alert('Erreur lors de la synchronisation');
  }
};

const resetForm = () => {
  form.value = {
    name: '',
    type: 'instagram',
    config: {
      hashtags: [],
      page_id: '',
      place_id: '',
    }
  };
};

const getPlatformIcon = (type) => {
  const icons = {
    instagram: '📷',
    facebook: '👍',
    twitter: '🐦',
    google_reviews: '⭐'
  };
  return icons[type] || '📱';
};

const getPlatformName = (type) => {
  const names = {
    instagram: 'Instagram',
    facebook: 'Facebook',
    twitter: 'Twitter / X',
    google_reviews: 'Google Reviews'
  };
  return names[type] || type;
};

const formatTime = (dateString) => {
  if (!dateString) return 'Jamais';
  const date = new Date(dateString);
  return date.toLocaleString('fr-FR');
};
</script>

