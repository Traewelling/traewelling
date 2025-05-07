<script setup lang="ts">
import {trans} from "laravel-vue-i18n";
import {ref} from "vue";
import {Api, MapProvider, UserProfileSettingsResource} from "../../../types/Api.gen";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});

const defaultUserData = ref(null as UserProfileSettingsResource | null);

const roles = {
  'open-beta': 'open-beta',
  'admin': 'admin',
}

const getDefaultUserData = () => {
  api.settings.getProfileSettings().then(res => {
    if (res.ok) {
      defaultUserData.value = res.data.data;
    } else {
      defaultUserData.value = null;
    }
  }).catch(() => {
    defaultUserData.value = null;
  });
}
getDefaultUserData();

console.log(Object.values(MapProvider))
</script>

<template>
  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-header">{{ trans('settings.title-profile') }}</div>

      <div class="card-body">
        <form class="d-grid gap-1" enctype="multipart/form-data" method="POST">
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
                       :value="defaultUserData?.username"
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
                     :value="defaultUserData?.displayName" required autocomplete="name"/>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.email') }}
            </label>
            <div class="col-md-6">
              <input id="email" type="email"
                     class="form-control" name="email"
                     :value="defaultUserData?.email" autocomplete="email"/>

            </div>
          </div>

          <div class="form-group row">
            <label for="mapprovider" class="col-md-4 col-form-label text-md-right">
              {{ trans('user.mapprovider') }}
            </label>
            <div class="col-md-6">
              <select class="form-select" name="mapprovider">
                <option v-for="provider in Object.values(MapProvider)"
                        :key="provider.value"
                        :selected="defaultUserData?.mapprovider === provider"
                >
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
                  :value="defaultUserData?.timezone"
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
              <select class="form-select" name="experimental" id="experimental">
                <option value="1" :selected="defaultUserData?.experimental === true">
                  {{ trans('settings.allow') }}
                </option>
                <option value="0" :selected="defaultUserData?.experimental === false">
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
