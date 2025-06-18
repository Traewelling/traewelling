<script setup lang="ts">
import {PropType, useTemplateRef} from "vue";
import {StatusResource} from "../../../../types/Api.gen";
import {trans} from "laravel-vue-i18n";
import {StatusHelper} from "../../../helpers/StatusHelper";
import {Notyf} from "notyf";
import ConfirmModal from "../../ConfirmModal.vue";
import {useUserStore} from "../../../stores/user";

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  },
});

const notyf = new Notyf({position: {x: "right", y: "bottom"}});
const emit = defineEmits(['confirm-delete', 'status-deleted']);
const user = useUserStore();

function share() {
  let helper = new StatusHelper(props.status);

  let shareText = props.status?.userDetails.id === user.user?.id ? helper.generateSocialText() : helper.getDescription();
  let shareUrl = helper.getShareUrl();

  if (navigator.share) {
    navigator.share({
      title: "Träwelling",
      text: shareText,
      url: shareUrl
    }).catch((error) => {
      console.error('Error sharing:', error);
    });
  } else {
    navigator.clipboard.writeText(shareText + ' ' + shareUrl).then(() => {
      notyf.success('Copied to clipboard');
    });
  }
}

function rideAlongUrl() {
  let queryParams = new URLSearchParams({
    tripId: props.status?.train.trip.toString(),
    lineName: props.status?.train.lineName,
    start: props.status?.train.origin.id.toString(),
    destination: props.status?.train.destination.id.toString(),
    departure: props.status?.train.origin.departurePlanned ? props.status?.train.origin.departurePlanned.toString() : '',
    idType: 'trwl',
    category: props.status?.train.category,
  });

  return `/stationboard/?${queryParams.toString()}`;
}

const delModal = useTemplateRef('delModal');

function showModal() {
  console.log(delModal.value);
  delModal.value?.show();
}
</script>

<template>
  <div class="dropdown">
    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
      &nbsp;
      <i class="fa fa-ellipsis-vertical" aria-hidden="true"></i>
      &nbsp;
    </a>
    <ul class="dropdown-menu">
      <li>
        <button class="dropdown-item" type="button" @click="share">
          <div class="dropdown-icon-suspense">
            <i class="fas fa-share" aria-hidden="true"></i>
          </div>
          {{ trans('menu.share') }}
        </button>
      </li>
      <template v-if="user.user">
        <template v-if="user.user.id == status.userDetails.id">
          <li>
            <button class="dropdown-item edit" type="button" :data-trwl-status-id="status.id">
              <div class="dropdown-icon-suspense">
                <i class="fas fa-edit" aria-hidden="true"></i>
              </div>
              {{ trans('edit') }}
            </button>
          </li>
          <li>
            <button class="dropdown-item" type="button" @click="showModal()">
              <div class="dropdown-icon-suspense">
                <i class="fas fa-trash" aria-hidden="true"></i>
              </div>
              {{ trans('delete') }}
            </button>
          </li>
        </template>
        <template v-else>
          <li>
            <a :href="rideAlongUrl()" class="dropdown-item">
              <div class="dropdown-icon-suspense">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
              </div>
              {{ trans('status.join') }}
            </a>
          </li>
          <!-- todo:mute-button -->
          <!-- todo:block-button -->
          <li>
            <a :href="`/report?subjectType=Status&subjectId=${status.id}`"
               class="dropdown-item">
              <div class="dropdown-icon-suspense">
                <i class="fas fa-flag" aria-hidden="true"></i>
              </div>
              {{ trans('status.report') }}
            </a>
          </li>
        </template>
        <li v-if="user?.isAdmin">
          <a :href="`/admin/status/edit?statusId=${status.id}`" class="dropdown-item">
            <div class="dropdown-icon-suspense">
              <i class="fas fa-tools" aria-hidden="true"></i>
            </div>
            Admin-Interface
          </a>
        </li>
      </template>
    </ul>
  </div>
  <ConfirmModal ref="delModal" title="modals.deleteStatus-title" @confirm="emit('confirm-delete')"/>
</template>
