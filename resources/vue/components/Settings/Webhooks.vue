<template>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
      <div class="card mb-3">
        <div class="card-header">{{ trans('settings.title-webhooks') }}</div>
        <div class="card-body table-responsive px-0">
          <p class="mx-4">
            {{ trans('settings.webhook-description') }}
          </p>

          <p v-if="webhooks.length === 0"
             class="text-danger mx-4"
             v-text="trans('settings.no-webhooks')"
          />

          <div v-else class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th>{{ trans('settings.client-name') }}</th>
                <th>{{ trans('settings.created') }}</th>
                <th>{{ trans('settings.webhook-event-notifications-description') }}</th>
                <th></th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="webhook in webhooks" :key="webhook.id">
                <td>{{ webhook.client.name }}</td>
                <td>{{ formatTimestamp(webhook.createdAt) }}</td>
                <td>
                  <ul v-if="webhook.events.length > 0">
                    <li v-for="event in webhook.events"
                        :key="event.id"
                        v-text="trans('settings.webhook_event.' + event.type)"
                    />
                  </ul>
                </td>
                <td>
                  <button class="btn btn-block btn-danger mx-0"
                          @click="deleteWebhook(webhook)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {trans} from "laravel-vue-i18n";
import axios from "axios";

export default {
  data() {
    return {
      webhooks: []
    }
  },
  methods: {
    trans,
    async fetchWebhooks() {
      axios.get('/api/v1/webhooks')
          .then(response => {
            this.webhooks = response.data.data;
          })
          .catch(error => {
            console.error(error);
          });
    },
    async deleteWebhook(webhook) {
      axios.delete(`/api/v1/webhooks/${webhook.id}`)
          .then(response => {
            this.webhooks = this.webhooks.filter(w => w.id !== webhook.id);
            notyf.success(trans('successfully-deleted'));
          })
          .catch(error => {
            console.error(error);
            notyf.error(trans('generic.error'));
          });
    },
    formatTimestamp(timestamp) {
      return new Date(timestamp).toLocaleString();
    }
  },
  mounted() {
    this.fetchWebhooks();
  }
}
</script>
