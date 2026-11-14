<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import { trans, transChoice } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { Dtm } from '../../../vue/helpers/DateTime';
import AppLayout from '../../layouts/AppLayout.vue';

interface AuthorizationRequest {
    client: {
        id: string;
        name: string;
        thirdParty: boolean;
        privacyPolicyUrl: string | null;
        createdAt: string;
        author: string;
        userCount: number;
    };
    scopes: string[];
    webhookEvents: string[] | null;
    state: string | null;
    authToken: string;
}

const authorization = JSON.parse(
    document.getElementById('page-data')?.textContent ?? 'null',
) as AuthorizationRequest | null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const requestText = computed(() => trans('menu.oauth_authorize.request', { application: '\u0000' }).split('\u0000'));
</script>

<template>
    <AppLayout>
        <div v-if="authorization" class="mx-auto flex w-full max-w-xl flex-col gap-4">
            <div v-if="authorization.client.thirdParty" class="alert alert-warning" role="alert">
                <TriangleAlert class="w-5 h-5 shrink-0" />
                <span>
                    {{ trans('menu.oauth_authorize.third_party') }}
                    {{ trans('menu.oauth_authorize.third_party.trust') }}
                </span>
                <a
                    href="https://help.traewelling.de/safety-and-security/apps/#drittanbieter-apps"
                    class="btn btn-sm"
                    target="_blank"
                    rel="noopener"
                >
                    {{ trans('menu.oauth_authorize.third_party.more') }}
                </a>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h1 class="text-2xl font-bold">{{ trans('menu.oauth_authorize.request_title') }}</h1>
                    <p>
                        {{ requestText[0] }}<strong>{{ authorization.client.name }}</strong
                        >{{ requestText[1] }}
                    </p>

                    <div v-if="authorization.scopes.length">
                        <p class="font-semibold">{{ trans('menu.oauth_authorize.scopes_title') }}</p>
                        <ul class="list-disc pl-5">
                            <li
                                v-for="scope in authorization.scopes"
                                :key="scope"
                                :class="{ 'text-error': scope.startsWith('extra') }"
                            >
                                {{ trans(`scopes.${scope}`) }}
                            </li>
                        </ul>
                    </div>

                    <div v-if="authorization.webhookEvents">
                        <p class="font-semibold">{{ trans('menu.oauth_authorize.webhook_request') }}</p>
                        <ul class="list-disc pl-5">
                            <li v-for="event in authorization.webhookEvents" :key="event">
                                {{ trans(`settings.webhook_event.${event}`) }}
                            </li>
                        </ul>
                    </div>

                    <div
                        v-if="authorization.client.thirdParty"
                        class="flex flex-col gap-1 text-sm text-base-content/70"
                    >
                        <span>
                            {{
                                trans('menu.oauth_authorize.application_information.author', {
                                    application: authorization.client.name,
                                    user: `@${authorization.client.author}`,
                                })
                            }}
                        </span>
                        <span>
                            {{
                                trans('menu.oauth_authorize.application_information.created_at', {
                                    time: new Dtm(authorization.client.createdAt).toRelative() ?? '',
                                })
                            }}
                        </span>
                        <span>
                            {{
                                transChoice(
                                    'menu.oauth_authorize.application_information.user_count',
                                    authorization.client.userCount,
                                )
                            }}
                        </span>
                    </div>

                    <a
                        v-if="authorization.client.privacyPolicyUrl"
                        class="link link-primary text-sm"
                        :href="authorization.client.privacyPolicyUrl"
                        target="_blank"
                        rel="noopener"
                    >
                        {{
                            trans('menu.oauth_authorize.application_information.privacy_policy', {
                                client: authorization.client.name,
                            })
                        }}
                    </a>

                    <div class="card-actions justify-end">
                        <form method="post" action="/oauth/authorize">
                            <input type="hidden" name="_token" :value="csrfToken" />
                            <input type="hidden" name="_method" value="DELETE" />
                            <input type="hidden" name="state" :value="authorization.state" />
                            <input type="hidden" name="client_id" :value="authorization.client.id" />
                            <input type="hidden" name="auth_token" :value="authorization.authToken" />
                            <button type="submit" class="btn">{{ trans('menu.oauth_authorize.cancel') }}</button>
                        </form>
                        <form method="post" action="/oauth/authorize">
                            <input type="hidden" name="_token" :value="csrfToken" />
                            <input type="hidden" name="state" :value="authorization.state" />
                            <input type="hidden" name="client_id" :value="authorization.client.id" />
                            <input type="hidden" name="auth_token" :value="authorization.authToken" />
                            <button type="submit" class="btn btn-primary">
                                {{ trans('menu.oauth_authorize.authorize') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
