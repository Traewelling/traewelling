<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['image-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const imageUpload = ref<File | null>(null);
const imageUploadBase64 = ref<string>('');

// upload image as base64 string
watch(imageUpload, (newValue) => {
    if (newValue) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (e.target) {
                imageUploadBase64.value = e.target.result as string;
            }
        };
        reader.readAsDataURL(newValue);
    }
});
function updateProfileImage() {
    const data = { image: imageUploadBase64.value };
    api.settings.uploadProfilePicture(data).then((response) => {
        response.json().then((data) => {
            imageUploadBase64.value = '';
            emits('image-updated', data.data);
        });
        modal.value?.close();
    });
}

function deleteProfileImage() {
    api.settings.deleteProfilePicture().then((response) => {
        response.json().then((data) => {
            imageUploadBase64.value = '';
            emits('image-updated', data.data);
        });
        modal.value?.close();
    });
}

function profileImage() {
    return imageUploadBase64.value || props.profile.profilePicture || `/@${props.profile.username}/picture`;
}
</script>

<template>
    <SettingsListRow :title="trans('settings.picture')" @click="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('settings.picture') }}</h3>
            <div class="grid grid-cols-3 gap-2 mt-4">
                <div class="avatar col-span-1">
                    <div class="w-24 rounded-full">
                        <img :src="profileImage()" alt="Profile Picture" />
                    </div>
                </div>
                <fieldset class="fieldset col-span-2">
                    <legend class="fieldset-legend">{{ trans('settings.upload-image') }}</legend>
                    <input
                        type="file"
                        class="file-input"
                        accept="image/*"
                        @change="
                            (event) =>
                                (imageUpload = (event.target as HTMLInputElement).files
                                    ? (event.target as HTMLInputElement).files![0]
                                    : null)
                        "
                    />
                    <label class="label">{{ trans('settings.upload-image.image-size') }}</label>
                </fieldset>
            </div>

            <form method="dialog">
                <div class="modal-action">
                    <button type="button" class="btn btn-outline btn-error me-auto" @click="deleteProfileImage()">
                        {{ trans('settings.delete-profile-picture') }}
                    </button>
                    <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    <button class="btn btn-primary" @click.prevent="updateProfileImage()">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
