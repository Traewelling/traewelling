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

const api = new Api({baseUrl: window.location.origin + '/api/v1'});

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
  api.settings.updateProfileSettings(userData.value).then(res => {
    console.log(res);
    if (res.ok) {
      userData.value = mapData(res.data.data);
    }
  }).catch((res) => {
    console.error(res);
    if (res.status === 422) {
      // Handle validation errors
      const errors = res.error.errors;
      for (const field in errors) {
        if (errors.hasOwnProperty(field)) {
          console.error(`${field}: ${errors[field].join(', ')}`);
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
          <div class="form-group row">
            <label for="name" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.username') }}
            </label>

            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text">@</span>
                <input id="username" type="text"
                       class="form-control"
                       name="username"
                       v-model="userData.username"
                       required
                />
              </div>
            </div>
          </div>

          <div class="form-group row">
            <label for="name" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.displayname') }}
            </label>
            <div class="col-md-6">
              <input id="name" type="text"
                     class="form-control" name="name"
                     v-model="userData.displayName" required autocomplete="name"/>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.email') }}
            </label>
            <div class="col-md-6">
              <input id="email" type="email"
                     class="form-control" name="email"
                     v-model="userData.email" required
                     autocomplete="email"/>

            </div>
          </div>

          <div class="form-group row">
            <label for="mapprovider" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.mapprovider') }}
            </label>
            <div class="col-md-6">
              <select class="form-select" name="mapprovider" v-model="userData.mapProvider">
                <option v-for="provider in Object.values(MapProvider)" :key="provider" :value="provider">
                  {{ trans('map-providers.' + provider) }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label for="timezone" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.timezone') }}
            </label>
            <div class="col-md-6">
              <input
                  class="form-control"
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
