<template>
  <Layout title="Analytics">
    <!-- Period Selector -->
    <div class="mb-6 flex justify-end">
      <select v-model="period" @change="loadAnalytics" class="input w-48">
        <option value="day">Dernières 24h</option>
        <option value="week">Dernière semaine</option>
        <option value="month">Dernier mois</option>
        <option value="year">Dernière année</option>
      </select>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <StatCard title="Vues" :value="stats.views || 0" icon="👁️" color="blue" />
      <StatCard title="Clics" :value="stats.clicks || 0" icon="🖱️" color="green" />
      <StatCard title="Interactions" :value="stats.interactions || 0" icon="⚡" color="purple" />
      <StatCard title="Chargements" :value="stats.widget_load || 0" icon="📊" color="orange" />
    </div>

    <!-- Platform Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="card">
        <h3 class="text-lg font-semibold mb-4">Statistiques par plateforme</h3>
        <div class="space-y-3">
          <div v-for="platform in platformStats" :key="platform.platform" class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ getPlatformIcon(platform.platform) }}</span>
              <div>
                <p class="font-medium">{{ getPlatformName(platform.platform) }}</p>
                <p class="text-sm text-gray-500">{{ platform.posts }} posts</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-semibold text-gray-900">{{ formatNumber(platform.likes + platform.comments + platform.shares) }}</p>
              <p class="text-xs text-gray-500">engagement</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Posts -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-4">Top Posts</h3>
        <div class="space-y-3">
          <div v-for="(post, index) in topPosts" :key="post.id" class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold text-sm">
              {{ index + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">{{ post.author_name }}</p>
              <p class="text-xs text-gray-600 truncate">{{ post.content }}</p>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                <span>❤️ {{ post.likes_count }}</span>
                <span>💬 {{ post.comments_count }}</span>
                <span>🔁 {{ post.shares_count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Engagement Chart Placeholder -->
    <div class="card">
      <h3 class="text-lg font-semibold mb-4">Évolution de l'engagement</h3>
      <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
        <p class="text-gray-500">Graphique d'engagement (À implémenter avec Chart.js)</p>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Layout from '../../Components/Layout.vue';
import StatCard from '../../Components/StatCard.vue';

const period = ref('week');
const stats = ref({});
const platformStats = ref([]);
const topPosts = ref([]);

onMounted(() => {
  loadAnalytics();
});

const loadAnalytics = async () => {
  try {
    const [analyticsRes, platformRes] = await Promise.all([
      axios.get(`/api/analytics?period=${period.value}`),
      axios.get(`/api/analytics/platform?period=${period.value}`)
    ]);

    stats.value = analyticsRes.data.events || {};
    platformStats.value = platformRes.data.platforms || [];
    topPosts.value = analyticsRes.data.top_posts || [];
  } catch (error) {
    console.error('Failed to load analytics', error);
  }
};

const getPlatformIcon = (platform) => {
  const icons = {
    instagram: '📷',
    facebook: '👍',
    twitter: '🐦',
    google: '⭐'
  };
  return icons[platform] || '📱';
};

const getPlatformName = (platform) => {
  const names = {
    instagram: 'Instagram',
    facebook: 'Facebook',
    twitter: 'Twitter / X',
    google: 'Google Reviews'
  };
  return names[platform] || platform;
};

const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num?.toString() || '0';
};
</script>

