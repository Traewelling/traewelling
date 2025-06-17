<script setup lang="ts">
import {PropType} from "vue";
import {StatusResource, UserAuthResource} from "../../../../types/Api.gen";
import {trans} from "laravel-vue-i18n";
import {StatusHelper} from "../../../helpers/StatusHelper";
import {Notyf} from "notyf";
import {RoleHelper} from "../../../helpers/RoleHelper";

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  },
  authenticatedUser: {
    type: Object as PropType<UserAuthResource | null>,
    default: null
  }
});

const notyf = new Notyf({position: {x: "right", y: "bottom"}});

function share() {
  let helper = new StatusHelper(props.status);

  let shareText = props.status?.userDetails.id === props.authenticatedUser?.id ? helper.generateSocialText() : helper.getDescription();
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

</script>

<template>
  <div class="dropdown">
    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa fa-ellipsis-vertical" aria-hidden="true"></i>
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
      <template v-if="authenticatedUser">
        <template v-if="authenticatedUser.id === status.userDetails.id">
          <li>
            <button class="dropdown-item edit" type="button" :data-trwl-status-id="status.id">
              <div class="dropdown-icon-suspense">
                <i class="fas fa-edit" aria-hidden="true"></i>
              </div>
              {{ trans('edit') }}
            </button>
          </li>
          <li>
            <button class="dropdown-item delete" type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-status-delete"
                    onclick="document.querySelector('#modal-status-delete input[name=\'statusId\']').value = '{{status.id}}';">
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
        <li v-if="new RoleHelper(authenticatedUser).admin()">
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
</template>
