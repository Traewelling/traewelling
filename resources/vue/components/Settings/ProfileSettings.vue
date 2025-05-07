<script setup lang="ts">
import {trans} from "laravel-vue-i18n";
import {ref} from "vue";
import {
  Api,
  FriendCheckinSetting,
  MapProvider,
  MastodonVisibility,
  StatusVisibility,
  UpdateProfileInformationRequest,
  UserProfileSettingsResource
} from "../../../types/Api.gen";
import Input from "./Partials/Input.vue";
import Select from "./Partials/Select.vue";
import {SelectOption} from "./Partials/SelectOption";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const providers = Object.values(MapProvider).map((provider) => {
  return {value: provider, translationKey: `map-providers.${provider}`} as SelectOption
});


const errors = ref({} as any);

const userData = ref(
    {
      username: "",
      displayName: "",
      privateProfile: false,
      preventIndex: false,
      privacyHideDays: 1,
      defaultStatusVisibility: StatusVisibility.Value0,
      mastodonVisibility: MastodonVisibility.Value0,
      mapProvider: MapProvider.Cargo,
      friendCheckin: FriendCheckinSetting.Value0,
      likesEnabled: false,
      pointsEnabled: false,
      bio: "",
      experimental: false,
      profileLinks: [],
      email: "",
      timezone: "",
    } as UpdateProfileInformationRequest
);

const mapData = (data: UserProfileSettingsResource) => {
  return {
    username: data.username,
    displayName: data.displayName,
    privateProfile: data.privateProfile,
    preventIndex: data.preventIndex,
    privacyHideDays: data.privacyHideDays,
    defaultStatusVisibility: data.defaultStatusVisibility,
    mastodonVisibility: data.mastodonVisibility,
    mapProvider: data.mapProvider,
    friendCheckin: data.friendCheckin,
    likesEnabled: data.likesEnabled,
    pointsEnabled: data.pointsEnabled,
    bio: data.bio,
    experimental: data.experimental,
    profileLinks: data.profileLinks,
    email: data.email,
    timezone: data.timezone
  } as UpdateProfileInformationRequest;
}

const getDefaultUserData = () => {
  api.settings.getProfileSettings().then(res => {
    if (res.ok && res.data.data !== undefined) {
      userData.value = mapData(res.data.data);
    }
  }).catch(() => {
  });
}

const updateProfile = () => {
  errors.value = {};
  api.settings.updateProfileSettings(userData.value).then(res => {
    console.log(res);
    if (res.ok) {
      userData.value = mapData(res.data.data);
    }
  }).catch((res) => {
    console.error(res);
    if (res.status === 422) {
      // Handle validation errors
      errors.value = res.error.errors;

      for (const field in errors.value) {
        if (errors.value.hasOwnProperty(field)) {
          console.error(`${field}: ${errors.value[field].join(', ')}`);
        }
      }
    } else {
      // Handle other errors
      console.error(res);
    }
    console.error(res)
  });
}

getDefaultUserData();
</script>

<template>
  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-header">{{ trans('settings.title-profile') }}</div>

      <div class="card-body">
        <form class="d-grid gap-1" @submit.prevent="updateProfile">
          <Input
              name="username"
              v-model="userData.username"
              prefix="@"
              :errors="errors.username"
              :title="trans('user.username')"
          />
          <Input
              name="name"
              v-model="userData.displayName"
              :errors="errors.displayName"
              :title="trans('user.displayname')"
              autocomplete="name"
          />
          <Input
              name="email"
              v-model="userData.email"
              :errors="errors.email"
              :title="trans('user.email')"
              autocomplete="email"
              required="true"
          />
          <Select
              :title="trans('user.mapprovider')"
              :name="'mapprovider'"
              v-model="userData.mapProvider"
              :options="providers"
              :errors="errors.mapProvider"
          />

          <div class="form-group row">
            <label for="timezone" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.timezone') }}
            </label>
            <div class="col-md-6">
              <input
                  class="form-control"
                  :class="{ 'is-invalid': errors.timezone }"
                  list="datalistOptions"
                  id="timezone"
                  name="timezone"
                  v-model="userData.timezone"
              >
              <datalist id="datalistOptions">
                <option v-for="timezone in Intl.supportedValuesOf('timeZone')"
                        :key="timezone"
                        :value="timezone"/>
              </datalist>
              <span v-if="errors.timezone" class="invalid-feedback" role="alert"><strong>{{
                  errors.timezone.join(', ')
                }}</strong></span>
            </div>
          </div>

          <div class="form-group row">
            <label for="experimental" class="col-md-4 col-form-label text-md-right">
              {{ trans('settings.experimental') }}
              <i
                  class="fas fa-info-circle"
                  :title="trans('settings.experimental.description')"
                  data-bs-toggle="tooltip"
              ></i>
            </label>
            <div class="col-md-6">
              <select class="form-select" name="experimental" id="experimental" v-model="userData.experimental">
                <option :value="true">
                  {{ trans('settings.allow') }}
                </option>
                <option :value="false">
                  {{ trans('settings.prevent') }}
                </option>
              </select>
            </div>
          </div>


          <div class="form-group row mt-3">
            <div class="col-md-6 offset-md-4">
              <button type="submit" class="btn btn-primary">
                {{ trans('settings.btn-update') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
