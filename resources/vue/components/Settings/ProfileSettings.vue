<script setup lang="ts">
import {trans} from "laravel-vue-i18n";
import {ref} from "vue";
import {
  Api,
  FriendCheckinSetting,
  MapProvider,
  MastodonVisibility,
  StatusVisibility,
  UpdateProfileInformationRequest
} from "../../../types/Api.gen";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});

const updateData = ref(
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

const getDefaultUserData = () => {
  api.settings.getProfileSettings().then(res => {
    if (res.ok && res.data.data !== undefined) {
      console.log(res.data.data.mapProvider);
      updateData.value.username = res.data.data.username;
      updateData.value.displayName = res.data.data.displayName;
      updateData.value.privateProfile = res.data.data.privateProfile;
      updateData.value.preventIndex = res.data.data.preventIndex;
      updateData.value.privacyHideDays = res.data.data.privacyHideDays;
      updateData.value.defaultStatusVisibility = res.data.data.defaultStatusVisibility;
      updateData.value.mastodonVisibility = res.data.data.mastodonVisibility;
      updateData.value.mapProvider = res.data.data.mapProvider;
      updateData.value.friendCheckin = res.data.data.friendCheckin;
      updateData.value.likesEnabled = res.data.data.likesEnabled;
      updateData.value.pointsEnabled = res.data.data.pointsEnabled;
      updateData.value.bio = res.data.data.bio;
      updateData.value.experimental = res.data.data.experimental;
      updateData.value.profileLinks = res.data.data.profileLinks;
      updateData.value.email = res.data.data.email;
      updateData.value.timezone = res.data.data.timezone;
    } else {
    }
  }).catch(() => {
  });
}
getDefaultUserData();
</script>

<template>
  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-header">{{ trans('settings.title-profile') }}</div>

      <div class="card-body">
        <form class="d-grid gap-1" @submit.prevent>
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
                       v-model="updateData.username"
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
                     v-model="updateData.displayName" required autocomplete="name"/>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.email') }}
            </label>
            <div class="col-md-6">
              <input id="email" type="email"
                     class="form-control" name="email"
                     v-model="updateData.email" required
                     autocomplete="email"/>

            </div>
          </div>

          <div class="form-group row">
            <label for="mapprovider" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.mapprovider') }}
            </label>
            <div class="col-md-6">
              <select class="form-select" name="mapprovider" v-model="updateData.mapProvider">
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
                  v-model="updateData.timezone"
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
              <select class="form-select" name="experimental" id="experimental" v-model="updateData.experimental">
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
