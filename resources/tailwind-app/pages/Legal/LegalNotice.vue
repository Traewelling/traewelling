<script setup lang="ts">
import { Mail, Phone, Scale } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { useConfigurationStore } from '../../../vue/stores/configuration';
import AppLayout from '../../layouts/AppLayout.vue';

const config = useConfigurationStore();
config.fetchData(!config.legalNotice);
</script>

<template>
    <AppLayout>
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <Scale class="w-6 h-6" />
                {{ trans('menu.legal-notice') }}
            </h1>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-base">Angaben gemäß § 5 DDG</h2>
                        <address class="not-italic">
                            {{ config.legalNotice?.name }}<br />
                            {{ config.legalNotice?.address1 }}<br />
                            {{ config.legalNotice?.address2 }}
                        </address>
                    </div>
                </div>
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-base">Kontakt</h2>
                        <p v-if="config.legalNotice?.email" class="flex items-center gap-2">
                            <Mail class="w-4 h-4 shrink-0" />
                            {{ config.legalNotice.email }}
                        </p>
                        <p v-if="config.legalNotice?.phone" class="flex items-center gap-2">
                            <Phone class="w-4 h-4 shrink-0" />
                            {{ config.legalNotice.phone }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="prose prose-sm max-w-none">
                        <h3>Haftung für Inhalte</h3>
                        <p>
                            Als Diensteanbieter sind wir gemäß § 7 Abs.1 DDG für eigene Inhalte auf diesen Seiten nach
                            den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 DDG sind wir als Diensteanbieter
                            jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen
                            oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
                        </p>
                        <p>
                            Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den
                            allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst
                            ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden
                            von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.
                        </p>

                        <h3>Haftung für Links</h3>
                        <p>
                            Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen
                            Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen.
                            Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der
                            Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf
                            mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung
                            nicht erkennbar.
                        </p>
                        <p>
                            Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete
                            Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von
                            Rechtsverletzungen werden wir derartige Links umgehend entfernen.
                        </p>

                        <h3>Urheberrecht</h3>
                        <p>
                            Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem
                            deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der
                            Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung
                            des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den
                            privaten, nicht kommerziellen Gebrauch gestattet.
                        </p>
                        <p>
                            Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die
                            Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche
                            gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden,
                            bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden
                            wir derartige Inhalte umgehend entfernen.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
