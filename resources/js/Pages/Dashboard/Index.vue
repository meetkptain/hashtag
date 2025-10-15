<template>
  <Layout title="Dashboard">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard title="Total Posts" :value="stats.totalPosts" icon="📝" color="blue" />
      <StatCard title="Nouveaux Posts" :value="stats.newPosts" icon="✨" color="green" />
      <StatCard title="Engagement Total" :value="stats.totalEngagement" icon="❤️" color="red" />
      <StatCard title="Flux Actifs" :value="stats.activeFeeds" icon="📡" color="purple" />
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Posts -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-4">Posts Récents</h3>
        <div class="space-y-4">
          <div v-for="post in recentPosts" :key="post.id" class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50">
            <div class="flex-shrink-0">
              <span class="text-2xl">{{ getPlatformIcon(post.platform) }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">{{ post.author_name }}</p>
              <p class="text-sm text-gray-600 truncate">{{ post.content }}</p>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                <span>❤️ {{ post.likes_count }}</span>
                <span>💬 {{ post.comments_count }}</span>
                <span>{{ formatTime(post.posted_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-4">Actions Rapides</h3>
        <div class="space-y-3">
          <Link href="/feeds" class="flex items-center justify-between p-4 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-xl">➕</span>
              </div>
              <div>
                <p class="font-medium text-gray-900">Ajouter un flux</p>
                <p class="text-sm text-gray-500">Instagram, Facebook, Twitter...</p>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </Link>

          <Link href="/settings" class="flex items-center justify-between p-4 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                <span class="text-xl">🎨</span>
              </div>
              <div>
                <p class="font-medium text-gray-900">Personnaliser le widget</p>
                <p class="text-sm text-gray-500">Thème, couleurs, animations...</p>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </Link>

          <div class="p-4 rounded-lg bg-gradient-to-r from-primary-500 to-purple-600 text-white">
            <p class="font-medium mb-2">Widget Code</p>
            <code class="text-xs bg-black/20 rounded px-2 py-1 block overflow-x-auto">
              &lt;script src="{{ widgetUrl }}"&gt;&lt;/script&gt;
            </code>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Layout from '../../Components/Layout.vue';
import StatCard from '../../Components/StatCard.vue';

const stats = ref({
  totalPosts: 0,
  newPosts: 0,
  totalEngagement: 0,
  activeFeeds: 0,
});

const recentPosts = ref([]);
const widgetUrl = window.location.origin + '/widget.js';

onMounted(async () => {
  // Load stats and recent posts
  try {
    const response = await axios.get('/api/analytics');
    stats.value = {
      totalPosts: response.data.posts.total,
      newPosts: response.data.posts.new,
      totalEngagement: response.data.engagement.total,
      activeFeeds: 0, // TODO: add to API
    };
  } catch (error) {
    console.error('Failed to load stats', error);
  }
});

const getPlatformIcon = (platform) => {
  const icons = {
    instagram: '📷',
    facebook: '👍',
    twitter: '🐦',
    google: '⭐'
  };
  return icons[platform] || '📱';
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return 'À l\'instant';
  if (diff < 3600) return Math.floor(diff / 60) + 'min';
  if (diff < 86400) return Math.floor(diff / 3600) + 'h';
  if (diff < 604800) return Math.floor(diff / 86400) + 'j';
  
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
};
</script>

