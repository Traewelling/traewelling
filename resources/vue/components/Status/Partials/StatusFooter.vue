<script setup lang="ts">

import {computed, PropType} from "vue";
import {StatusResource, UserAuthResource} from "../../../../types/Api.gen";
import {trans} from "laravel-vue-i18n";
import {IconHelper} from "../../../helpers/IconHelper";
import StatusContextMenu from "./StatusContextMenu.vue";
import {Dtm} from "../../../helpers/DateTime";
import {DateTime} from "luxon";

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  },
  authenticatedUser: {
    type: Object as PropType<UserAuthResource | null>,
    default: null
  },
});

function like() {
  // todo
}

const createdAt = computed(() => {
  const date = Dtm.fromISO(props.status.createdAt);
  if (date.dateTime.diffNow().as('days') < -1) {
    return trans('dates.-on-') + ' ' + date.toLocaleString(DateTime.TIME_SIMPLE);
  } else {
    return date.toRelative();
  }
});
</script>

<template>
  <div class="card-footer text-muted px-3 px-md-4">
    <ul class="list-inline float-end">
      <template v-if="status.isLikable">
        <li class="like-text list-inline-item me-1">
          <a
              href="#"
              class="like"
              :class="{'fas fa-heart': status.liked, 'far fa-heart': !status.liked, 'peach': status.userDetails.id === 18574}"
              @click="like()"
          >
            <span class="sr-only">{{ trans('action.like') }}</span>
          </a>
        </li>
        <li class="like-text list-inline-item">
        <span class="pl-1" :class="{'d-none': status.likes === 0}">
          {{ status.likes }}
        </span>
        </li>
      </template>
      <li class="like-text list-inline-item">
        <i class="fas" :class="IconHelper.getVisibilityIcon(status.visibility)"
           aria-hidden="true"
           data-bs-toggle="tooltip"
           data-bs-placement="top"
           :title="IconHelper.getVisibilityTooltip(status.visibility)"
        ></i>
      </li>
      <li class="like-text list-inline-item">
        <StatusContextMenu/>
      </li>
    </ul>

    <ul class="list-inline">
      <li class="d-lg-none list-inline-item">
        <a :href="`/@${status.userDetails.username}`">
          <img loading="lazy" :src="status.userDetails.profilePicture" class="profile-image"
               :alt="status.userDetails.username"/>
        </a>
      </li>
      <li class="list-inline-item me-1">
        <a :href="`/@${status.userDetails.username}`" class="username">
          {{ authenticatedUser?.id !== status.userDetails.id ? status.userDetails.username : trans('user.you') }}
        </a>
      </li>
      <li class="list-inline-item">
        <a :href="`/status/${status.id}`" class="status-link">
          {{ createdAt }}
        </a>
      </li>
    </ul>
  </div>
</template>>
