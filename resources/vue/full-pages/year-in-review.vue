<template>
  <div class="year-in-review" ref="container">
    <!-- Loading State -->
    <section v-if="loading" class="section section-loading">
      <div class="loader">
        <div class="train-icon">🚂</div>
        <p class="loading-text">{{ t('loading') }}</p>
      </div>
    </section>

    <!-- Error State -->
    <section v-else-if="error" class="section section-error">
      <div class="error-content">
        <div class="error-icon">⚠️</div>
        <h1>{{ t('error') }}</h1>
        <a href="/login" class="btn-login">Login</a>
      </div>
    </section>

    <!-- Main Content -->
    <template v-else>
      <!-- Hero Section -->
      <section class="section section-hero">
        <div class="hero-content">
          <div class="logo-badge">
            <span class="year-badge">{{ data.year }}</span>
          </div>
          <h1 class="hero-title">{{ t('welcome') }}</h1>
          <p class="hero-subtitle">{{ t('your_review') }}</p>
          <button class="btn-start" @click="scrollToNext">
            <span>{{ t('goto_yir') }}</span>
            <span class="arrow-down">↓</span>
          </button>
        </div>
        <div class="hero-bg-pattern"></div>
      </section>

      <!-- Welcome Section -->
      <section class="section section-welcome" ref="sectionWelcome">
        <h1 class="welcome-title" ref="welcomeTitle">
          {{ t('hello') }} <span class="highlight">{{ data.user?.name }}</span>!
        </h1>
        <h2 class="welcome-subtitle" ref="welcomeSubtitle">{{ data.year }} {{ t('longyear') }}</h2>
      </section>

      <!-- Minutes Section -->
      <section class="section section-minutes" ref="sectionMinutes">
        <div class="stat-container">
          <p class="stat-label" ref="minutesLabel1">{{ t('one.one') }}</p>
          <h1 class="stat-number" ref="minutesNumber">
            {{ animatedMinutes.toFixed(0) }}
          </h1>
          <p class="stat-unit" ref="minutesLabel2">{{ t('one.two') }}</p>
          <p class="stat-desc" ref="minutesLabel3">{{ t('one.three') }}</p>
        </div>
      </section>

      <!-- Intro Stats Section -->
      <section class="section section-intro-stats">
        <h1 class="intro-text">{{ t('two') }}</h1>
      </section>

      <!-- Your Year Section -->
      <section class="section section-your-year">
        <h1 class="your-year-title">{{ t('yourYear') }}</h1>
        <div class="stats-preview">
          <div class="stat-pill">
            <span class="stat-value">{{ data.count }}</span>
            <span class="stat-label">Journeys</span>
          </div>
          <div class="stat-pill">
            <span class="stat-value">{{ formatKm(data.distance?.total) }}</span>
            <span class="stat-label">km</span>
          </div>
          <div class="stat-pill">
            <span class="stat-value">{{ formatHours(data.duration?.total) }}</span>
            <span class="stat-label">hours</span>
          </div>
          <div class="stat-pill" v-if="data.totalDelay">
            <span class="stat-value">{{ data.totalDelay }}</span>
            <span class="stat-label">minutes delay</span>
          </div>
        </div>
      </section>

      <!-- Trip Count Section -->
      <section class="section section-trips" ref="sectionTrips">
        <div class="stat-card">
          <p class="stat-intro">{{ t('three.one') }}</p>
          <h1 class="stat-big-number" ref="tripCount">{{ animatedTrips.toFixed(0) }}</h1>
          <p class="stat-outro">{{ t('three.two') }}</p>
        </div>
      </section>

      <!-- Distance Section -->
      <section class="section section-distance" ref="sectionDistance">
        <div class="stat-card">
          <p class="stat-intro">{{ t('four.one') }}</p>
          <h1 class="stat-big-number">
            <span ref="distanceNumber">{{ animatedDistance.toFixed(0) }}</span>
            <span class="unit">km</span>
          </h1>
          <p class="stat-outro">{{ t('four.two') }}</p>
        </div>
      </section>

      <!-- Earth Circumference Section -->
      <section class="section section-earth">
        <div class="earth-visual">
          <div class="earth-icon">🌍</div>
          <div class="stat-content">
            <p class="stat-intro">{{ t('five.one') }}</p>
            <h1 class="stat-highlight">{{ earthCircumference }}x</h1>
            <p class="stat-outro">{{ t('five.two') }}</p>
          </div>
        </div>
      </section>

      <!-- Duration Section -->
      <section class="section section-duration" ref="sectionDuration">
        <div class="stat-card">
          <p class="stat-intro">{{ t('six.one') }}</p>
          <h1 class="stat-big-number">
            <span ref="durationNumber">{{ animatedDuration.toFixed(0) }}</span>
            <span class="unit">{{ t('six.two') }}</span>
          </h1>
          <p class="stat-outro">{{ t('six.three') }}</p>
        </div>
      </section>

      <!-- Vatican Railway Section -->
      <section class="section section-vatican">
        <div class="vatican-visual">
          <div class="vatican-icon">
            <span class="vatican-flag">🇻🇦</span>
            <span class="vatican-train">🚂</span>
          </div>
          <div class="stat-content">
            <p class="stat-intro">{{ t('seven.one') }}</p>
            <h1 class="stat-highlight">{{ vaticanMultiplier }}x</h1>
            <p class="stat-outro">{{ t('seven.two') }}</p>
            <p class="stat-small">{{ t('seven.three') }}</p>
          </div>
        </div>
      </section>

      <!-- Operators Section -->
      <section v-if="data.operators?.count" class="section section-operators">
        <div class="stat-card">
          <p class="stat-intro">{{ t('eight.one') }}</p>
          <h1 class="stat-big-number">{{ data.operators.count }}</h1>
          <p class="stat-outro">{{ t('eight.two') }}</p>
        </div>
      </section>

      <!-- Favorite Operator by Distance -->
      <section v-if="data.operators?.topByDistance" class="section section-fav-operator">
        <div class="operator-card">
          <p class="stat-intro">{{ t('nine.one') }}</p>
          <h1 class="operator-name">{{ data.operators.topByDistance.operator }}</h1>
          <p class="operator-stat">
            {{ formatKm(data.operators.topByDistance.distance) }} km
          </p>
        </div>
      </section>

      <!-- Favorite Operator by Duration -->
      <section
          v-if="data.operators?.topByDuration && data.operators.topByDuration.operator !== data.operators.topByDistance?.operator"
          class="section section-fav-operator-time">
        <div class="operator-card">
          <p class="stat-intro">{{ t('ten.one') }}</p>
          <h1 class="operator-name">{{ data.operators.topByDuration.operator }}</h1>
          <p class="operator-stat">
            {{ formatHours(data.operators.topByDuration.duration) }} {{ t('hours') }}
          </p>
        </div>
      </section>

      <!-- Top Lines Section -->
      <section v-if="data.lines?.topByDistance || data.lines?.topByDuration" class="section section-lines">
        <div class="lines-card">
          <h1 class="lines-title">{{ t('eleven.one') }}</h1>
          <p class="lines-subtitle">{{ t('eleven.two') }}</p>
          <div class="lines-list">
            <div v-if="data.lines?.topByDistance" class="line-item">
              <span class="line-name">{{ data.lines.topByDistance.line }}</span>
              <span class="line-stat">{{ formatKm(data.lines.topByDistance.distance) }} km</span>
            </div>
            <template
                v-if="data.lines?.topByDuration && data.lines.topByDuration.line !== data.lines.topByDistance?.line">
              <p class="lines-and">{{ t('eleven.three') }}</p>
              <div class="line-item">
                <span class="line-name">{{ data.lines.topByDuration.line }}</span>
                <span class="line-stat">{{ formatHours(data.lines.topByDuration.duration) }} {{ t('hours') }}</span>
              </div>
            </template>
          </div>
        </div>
      </section>

      <!-- Longest Trip -->
      <section v-if="longestTrip" class="section section-longest">
        <div class="trip-card">
          <div class="trip-badge">{{ t('thirteen.badge') }}</div>
          <p class="trip-date">{{ formatDate(longestTrip.origin?.departure) }}</p>
          <h2 class="trip-line">{{ longestTrip.lineName }}</h2>
          <div class="trip-route">
            <span class="trip-origin">{{ longestTrip.origin?.name }}</span>
            <span class="trip-arrow">→</span>
            <span class="trip-dest">{{ longestTrip.destination?.name }}</span>
          </div>
          <p class="trip-stat">{{ formatKm(longestTrip.distance) }} km</p>
        </div>
      </section>

      <!-- Longest Duration Trip -->
      <section v-if="longestDurationTrip && longestDurationTrip.trip !== longestTrip?.trip"
               class="section section-longest-duration">
        <div class="trip-card">
          <div class="trip-badge">{{ t('thirteen_duration.badge') }}</div>
          <p class="trip-date">{{ formatDate(longestDurationTrip.origin?.departure) }}</p>
          <h2 class="trip-line">{{ longestDurationTrip.lineName }}</h2>
          <div class="trip-route">
            <span class="trip-origin">{{ longestDurationTrip.origin?.name }}</span>
            <span class="trip-arrow">→</span>
            <span class="trip-dest">{{ longestDurationTrip.destination?.name }}</span>
          </div>
          <p class="trip-stat">{{ longestDurationTrip.duration }} {{ t('one.two') }}</p>
        </div>
      </section>

      <!-- Fastest Trip -->
      <section v-if="fastestTrip" class="section section-fastest">
        <div class="trip-card">
          <div class="trip-badge">{{ t('fourteen.badge') }}</div>
          <p class="trip-date">{{ formatDate(fastestTrip.origin?.departure) }}</p>
          <h2 class="trip-line">{{ fastestTrip.lineName }}</h2>
          <div class="trip-route">
            <span class="trip-origin">{{ fastestTrip.origin?.name }}</span>
            <span class="trip-arrow">→</span>
            <span class="trip-dest">{{ fastestTrip.destination?.name }}</span>
          </div>
          <p class="trip-stat">Ø {{ calculateSpeed(fastestTrip) }} km/h</p>
        </div>
      </section>

      <!-- Slowest Trip -->
      <section v-if="slowestTrip" class="section section-slowest">
        <div class="trip-card">
          <div class="trip-badge">{{ t('fifteen.badge') }}</div>
          <p class="trip-date">{{ formatDate(slowestTrip.origin?.departure) }}</p>
          <h2 class="trip-line">{{ slowestTrip.lineName }}</h2>
          <div class="trip-route">
            <span class="trip-origin">{{ slowestTrip.origin?.name }}</span>
            <span class="trip-arrow">→</span>
            <span class="trip-dest">{{ slowestTrip.destination?.name }}</span>
          </div>
          <p class="trip-stat">Ø {{ calculateSpeed(slowestTrip) }} km/h</p>
        </div>
      </section>

      <!-- Most Delayed Trip -->
      <section v-if="mostDelayedTrip" class="section section-delayed">
        <div class="trip-card delayed-card">

          <div class="trip-badge">{{ t('delayed.badge') }}</div>

          <div class="delay-highlight">
            <span class="delay-value">+{{ mostDelayedTrip.computedDelay }}</span>
          </div>

          <p class="trip-date">{{ formatDate(mostDelayedTrip.origin?.departure) }}</p>
          <h2 class="trip-line">{{ mostDelayedTrip.lineName }}</h2>

          <div class="trip-route">
            <span class="trip-origin">{{ mostDelayedTrip.origin?.name }}</span>
            <span class="trip-arrow">→</span>
            <span class="trip-dest">{{ mostDelayedTrip.destination?.name }}</span>
          </div>
        </div>
      </section>


      <!-- Most Visited Station -->
      <section v-if="data.topDestinations?.length" class="section section-top-station">
        <div class="station-card">
          <div class="station-icon">📍</div>
          <p class="stat-intro">{{ t('sixteen.one') }}</p>
          <h1 class="station-name">{{ data.topDestinations[0].station?.name }}</h1>
          <p class="station-count">{{ data.topDestinations[0].count }}x</p>
        </div>
      </section>

      <!-- Lonely Stations (Combined) -->
      <section v-if="data.lonelyStations?.length" class="section section-lonely">
        <div class="lonely-card">
          <h1 class="lonely-title">{{ t('seventeen.one') }}</h1>
          <p class="lonely-subtitle">{{ t('seventeen.two') }}</p>
          <p class="lonely-count">{{ data.lonelyStations.length }} {{ t('eighteen.one') }}</p>
          <div class="lonely-list">
            <template v-if="data.lonelyStations.length > 5">
              <span v-for="station in data.lonelyStations.slice(0, 5)" :key="station.station?.id"
                    class="lonely-station">
                {{ station.station?.name }}
              </span>
              <span class="lonely-more">{{ t('eighteen.two') }}</span>
            </template>
            <template v-else>
              <span v-for="station in data.lonelyStations" :key="station.station?.id" class="lonely-station">
                {{ station.station?.name }}
              </span>
            </template>
          </div>
          <p class="lonely-disclaimer">* {{ t('seventeen.disclaimer') }}</p>
        </div>
      </section>

      <!-- Most Liked Statuses -->
      <section v-if="data.mostLikedStatuses?.length" class="section section-liked">
        <div class="liked-card">
          <h1 class="liked-title">{{ t('liked.title') }}</h1>
          <div class="liked-list">
            <div v-for="statusObj in data.mostLikedStatuses.slice(0, 3)" :key="statusObj.status.id" class="liked-item">
              <span class="liked-count">{{ statusObj.status.likes }}x ❤️&nbsp;</span>
              <span class="liked-line">{{ statusObj.status.train?.lineName }}&nbsp;</span>
              <span class="liked-route">{{ statusObj.status.train?.origin?.name }} → {{ statusObj.status.train?.destination?.name }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Thank You Section -->
      <section class="section section-thanks">
        <div class="thanks-content">
          <h1 class="thanks-title">{{ t('twenty.one') }}</h1>
          <p class="thanks-subtitle">{{ t('twenty.two') }}</p>
          <div class="thanks-heart">❤️</div>
          <div class="thanks-logo">Träwelling</div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import {computed, onMounted, onUnmounted, ref} from 'vue'
import gsap from 'gsap'
import {ScrollTrigger} from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

// Translations
const translations = {
  de: {
    loading: "Lädt deine Reisen...",
    error: "Es scheint einen Fehler gegeben zu haben. Bist du sicher, dass du angemeldet bist?",
    welcome: "Träwelling Jahresrückblick",
    your_review: "Dein persönlicher Jahresrückblick",
    goto_yir: "Zu meinem Jahresüberblick",
    hello: "Hallo",
    longyear: "war ein langes Jahr.",
    one: {
      one: "Das waren",
      two: "Minuten",
      three: "in denen du immer etwas erlebt hast."
    },
    two: "In diesen 365 Tagen warst du viel unterwegs mit uns!",
    yourYear: "Das war dein Jahr bei uns in Zahlen, Daten und Fakten:",
    three: {
      one: "Du warst mit uns ganze",
      two: "Fahrten unterwegs!"
    },
    four: {
      one: "Du bist dieses Jahr in Summe",
      two: "gefahren!"
    },
    five: {
      one: "Das entspricht etwa",
      two: "dem Erdumfang!"
    },
    hours: "Stunden",
    six: {
      one: "Du warst",
      two: "Stunden",
      three: "in unterschiedlichsten Verkehrsmitteln unterwegs"
    },
    seven: {
      one: "Das entspricht",
      two: "dem Schienennetz des Vatikan!",
      three: "(Streckenlänge: 400m)"
    },
    eight: {
      one: "In Summe warst du mit",
      two: "Eisenbahn-Unternehmen unterwegs."
    },
    nine: {
      one: "Dein liebstes darunter war"
    },
    ten: {
      one: "Am meisten Zeit verbracht mit"
    },
    eleven: {
      one: "Du Pendler!",
      two: "Deine meistgenutzten Linien dieses Jahr waren:",
      three: "und"
    },
    thirteen: {
      badge: "Längste Fahrt"
    },
    thirteen_duration: {
      badge: "Längste Fahrt (Zeit)"
    },
    fourteen: {
      badge: "Schnellste Fahrt"
    },
    fifteen: {
      badge: "Langsamste Fahrt"
    },
    delayed: {
      badge: "Meiste Verspätung"
    },
    sixteen: {
      one: "Am häufigsten warst du in"
    },
    seventeen: {
      one: "Du bist ein absoluter Weltenbummler!",
      two: "Es gibt Orte, dort war niemand außer du!",
      disclaimer: "Aufgrund von Datenfehlern ist diese Statistik mit Vorsicht zu genießen, da das ID-Matching nicht immer korrekt ist."
    },
    eighteen: {
      one: "Orte!",
      two: "Nur um ein paar zu nennen..."
    },
    liked: {
      title: "Deine beliebtesten Check-ins"
    },
    twenty: {
      one: "Danke, dass du Träwelling nutzt!",
      two: "Ohne euch würde das alles nur halb so viel Spaß machen!"
    }
  },
  en: {
    loading: "Loading your travels...",
    error: "There seems to be an error. Are you sure you're logged in?",
    welcome: "Träwelling Year in Review",
    your_review: "Your personal year in review",
    goto_yir: "To my year in review",
    hello: "Hello",
    longyear: "was a long year.",
    one: {
      one: "Those were",
      two: "minutes",
      three: "in which you always had something going on."
    },
    two: "In these 365 days, you were on the go with us a lot!",
    yourYear: "This is your year with us in numbers, dates and facts:",
    three: {
      one: "You travelled a total of",
      two: "trips with us!"
    },
    four: {
      one: "In total you travelled",
      two: "this year!"
    },
    five: {
      one: "That's about the same as",
      two: "the circumference of the earth!"
    },
    hours: "hours",
    six: {
      one: "You were travelling",
      two: "hours",
      three: "in different means of transportation"
    },
    seven: {
      one: "That's",
      two: "the Vatican's railway network!",
      three: "(Track length: 400m)"
    },
    eight: {
      one: "In total, you were traveling with",
      two: "operators."
    },
    nine: {
      one: "Your favourite among them was"
    },
    ten: {
      one: "Most time spent with"
    },
    eleven: {
      one: "You commuter!",
      two: "Your most used lines this year were:",
      three: "and"
    },
    thirteen: {
      badge: "Longest trip (distance)"
    },
    thirteen_duration: {
      badge: "Longest trip (time)"
    },
    fourteen: {
      badge: "Fastest trip"
    },
    fifteen: {
      badge: "Slowest trip"
    },
    delayed: {
      badge: "Most delayed trip"
    },
    sixteen: {
      one: "Most often you were in"
    },
    seventeen: {
      one: "You're an absolute globetrotter!",
      two: "There are places where no one has been but you!",
      disclaimer: "Due to data inconsistencies, this statistic should be taken with caution as ID matching is not always accurate."
    },
    eighteen: {
      one: "places!",
      two: "Just to name a few..."
    },
    liked: {
      title: "Your most liked check-ins"
    },
    twenty: {
      one: "Thank you for using Träwelling!",
      two: "Without you all this would only be half as much fun!"
    }
  }
}

// Reactive State
const loading = ref(true)
const error = ref(false)
const container = ref(null)
const locale = ref(navigator.language?.startsWith('de') ? 'de' : 'en')

// Animated values
const animatedMinutes = ref(0)
const animatedTrips = ref(0)
const animatedDistance = ref(0)
const animatedDuration = ref(0)

// Data
const data = ref({
  year: new Date().getFullYear(),
  user: {name: ''},
  count: 0,
  distance: {total: 0, averagePerDay: 0},
  duration: {total: 0, averagePerDay: 0},
  totalDelay: 0,
  operators: {count: 0, topByDistance: null, topByDuration: null},
  lines: {topByDistance: null, topByDuration: null},
  longestTrips: null,
  fastestTrips: null,
  slowestTrips: null,
  mostDelayedArrivals: null,
  topDestinations: [],
  lonelyStations: [],
  mostLikedStatuses: []
})

// Translation function
const t = (key) => {
  const keys = key.split('.')
  let result = translations[locale.value]
  for (const k of keys) {
    result = result?.[k]
  }
  return result || key
}

// Computed Properties
const earthCircumference = computed(() => {
  const km = (data.value.distance?.total || 0) / 1000
  return (km / 40074).toFixed(2)
})

const vaticanMultiplier = computed(() => {
  const km = (data.value.distance?.total || 0) / 1000
  return (km / 0.4).toFixed(0) // Vatican railway is 400m = 0.4km
})

const longestTrip = computed(() => {
  return data.value.longestTrips?.distance?.train || null
})

const longestDurationTrip = computed(() => {
  return data.value.longestTrips?.duration?.train || null
})

const fastestTrip = computed(() => {
  return data.value.fastestTrips?.train || null
})

const slowestTrip = computed(() => {
  return data.value.slowestTrips?.train || null
})

const mostDelayedTrip = computed(() => {
  const trip = data.value.mostDelayedArrivals?.train
  console.log(trip);
  if (!trip) return null
  console.log(calculateDelay(trip))
  return {
    ...trip,
    computedDelay: calculateDelay(trip)
  }
})

// Helper Functions
const formatKm = (meters) => {
  if (!meters) return '0'
  return Math.round(meters / 1000).toLocaleString('de-DE')
}

const formatHours = (minutes) => {
  if (!minutes) return '0'
  return Math.round(minutes / 60).toLocaleString('de-DE')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const calculateSpeed = (trip) => {
  if (!trip?.distance || !trip?.duration) return 0
  const km = trip.distance / 1000
  const hours = trip.duration / 60
  if (hours === 0) return 0
  return Math.round(km / hours)
}

const scrollToNext = () => {
  const sections = container.value?.querySelectorAll('.section')
  if (sections?.length > 1) {
    sections[1].scrollIntoView({behavior: 'smooth'})
  }
}

// GSAP Animations
const setupAnimations = () => {
  ScrollTrigger.defaults({
    scroller: container.value,
    toggleActions: 'play none none reverse'
  })

  // Welcome section animation
  gsap.from('.welcome-title', {
    scrollTrigger: {
      trigger: '.section-welcome',
      start: 'top center',
    },
    opacity: 0,
    y: 50,
    duration: 1
  })

  gsap.from('.welcome-subtitle', {
    scrollTrigger: {
      trigger: '.section-welcome',
      start: 'top center',
    },
    opacity: 0,
    y: 30,
    duration: 1,
    delay: 0.5
  })

  // Minutes counter animation
  ScrollTrigger.create({
    trigger: '.section-minutes',
    start: 'top center',
    onEnter: () => {
      gsap.to(animatedMinutes, {
        value: data.value.duration.total,
        duration: 2,
        ease: 'power2.out'
      })
    }
  })

  // Trips counter animation
  ScrollTrigger.create({
    trigger: '.section-trips',
    start: 'top center',
    onEnter: () => {
      gsap.to(animatedTrips, {
        value: data.value.count,
        duration: 1.5,
        ease: 'power2.out'
      })
    }
  })

  // Distance counter animation
  ScrollTrigger.create({
    trigger: '.section-distance',
    start: 'top center',
    onEnter: () => {
      gsap.to(animatedDistance, {
        value: (data.value.distance?.total || 0) / 1000,
        duration: 2,
        ease: 'power2.out'
      })
    }
  })

  // Duration counter animation
  ScrollTrigger.create({
    trigger: '.section-duration',
    start: 'top center',
    onEnter: () => {
      gsap.to(animatedDuration, {
        value: (data.value.duration?.total || 0) / 60,
        duration: 2,
        ease: 'power2.out'
      })
    }
  })

  // Fade in animations for various sections
  const fadeInSections = [
    '.section-earth',
    '.section-vatican',
    '.section-operators',
    '.section-fav-operator',
    '.section-fav-operator-time',
    '.section-lines',
    '.section-longest',
    '.section-longest-duration',
    '.section-fastest',
    '.section-slowest',
    '.section-delayed',
    '.section-top-station',
    '.section-lonely',
    '.section-liked',
    '.section-thanks'
  ]

  fadeInSections.forEach(selector => {
    const element = document.querySelector(selector)
    if (element) {
      gsap.from(selector, {
        scrollTrigger: {
          trigger: selector,
          start: 'top 80%',
        },
        opacity: 0,
        y: 40,
        duration: 0.8
      })
    }
  })
}

// Fetch Data
const fetchData = async () => {
  try {
    const response = await fetch('/api/v1/year-in-review', {
      headers: {
        Accept: 'application/json'
      }
    })

    if (response.redirected || !response.ok) {
      throw new Error('Fetch failed')
    }

    const json = await response.json()
    data.value = json
    loading.value = false

    // Setup animations after data is loaded
    setTimeout(() => {
      setupAnimations()
    }, 100)
  } catch (err) {
    console.error('Error fetching data:', err)
    error.value = true
    loading.value = false
  }
}

const calculateDelay = (trip) => {
  if (!trip?.destination) return 0

  const stop = trip.destination

  const bestArrival =
      trip?.manualArrival ||
      stop?.arrivalReal ||
      stop?.arrivalPlanned ||
      null

  const plannedArrival = stop?.arrivalPlanned

  if (!bestArrival || !plannedArrival) return 0

  const a = new Date(bestArrival)
  const p = new Date(plannedArrival)

  const diff = Math.round((a - p) / 60000)
  return diff > 0 ? diff : 0
}


// Lifecycle
onMounted(() => {
  fetchData()
})

onUnmounted(() => {
  ScrollTrigger.getAll().forEach(trigger => trigger.kill())
})
</script>

<style scoped>
@import url('../../../public/fonts/Nunito/Nunito.css');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.year-in-review {
  --primary: #c72730;
  --primary-dark: #a01f27;
  --accent: #ff6b6b;
  --bg-dark: #0a0a0f;
  --bg-darker: #050508;
  --text-primary: #ffffff;
  --text-secondary: rgba(255, 255, 255, 0.7);
  --text-muted: rgba(255, 255, 255, 0.5);

  font-family: 'Sora', sans-serif;
  background: var(--bg-dark);
  color: var(--text-primary);
  height: 100vh;
  overflow-y: scroll;
  scroll-snap-type: y mandatory;
  scroll-behavior: smooth;
}

.section {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  scroll-snap-align: start;
  position: relative;
  overflow: hidden;
}

/* Loading Section */
.section-loading {
  background: var(--bg-darker);
}

.loader {
  text-align: center;
}

.train-icon {
  font-size: 4rem;
  animation: bounce 1s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}

.loading-text {
  margin-top: 1rem;
  font-size: 1.2rem;
  color: var(--text-secondary);
}

/* Error Section */
.section-error {
  background: var(--bg-darker);
}

.error-content {
  text-align: center;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-content h1 {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  color: var(--text-secondary);
}

.btn-login {
  display: inline-block;
  padding: 1rem 2rem;
  background: var(--primary);
  color: white;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-login:hover {
  background: var(--primary-dark);
  transform: translateY(-2px);
}

/* Hero Section */
.section-hero {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  position: relative;
}

.hero-bg-pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
  radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.08) 0%, transparent 40%);
  pointer-events: none;
}

.hero-content {
  text-align: center;
  z-index: 1;
}

.logo-badge {
  margin-bottom: 2rem;
}

.year-badge {
  display: inline-block;
  padding: 0.5rem 1.5rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50px;
  font-size: 1.2rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.hero-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2.5rem, 8vw, 5rem);
  font-weight: 700;
  line-height: 1.1;
  margin-bottom: 1rem;
  text-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 3rem;
}

.btn-start {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: white;
  color: var(--primary);
  border: none;
  border-radius: 50px;
  font-family: inherit;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-start:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.arrow-down {
  animation: float 2s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(5px);
  }
}

/* Welcome Section */
.section-welcome {
  background: var(--bg-darker);
}

.welcome-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 6vw, 4rem);
  font-weight: 700;
  text-align: center;
}

.welcome-title .highlight {
  color: var(--accent);
}

.welcome-subtitle {
  font-size: clamp(1.5rem, 4vw, 2.5rem);
  color: var(--text-secondary);
  margin-top: 1rem;
  font-weight: 300;
}

/* Minutes Section */
.section-minutes {
  background: linear-gradient(180deg, var(--bg-darker) 0%, var(--bg-dark) 100%);
}

.stat-container {
  text-align: center;
}

.stat-label {
  font-size: 1.2rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.stat-number {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(4rem, 15vw, 10rem);
  font-weight: 700;
  color: var(--accent);
  line-height: 1;
}

.stat-unit {
  font-size: 1.5rem;
  color: var(--text-secondary);
  margin: 0.5rem 0;
}

.stat-desc {
  font-size: 1rem;
  color: var(--text-muted);
  max-width: 400px;
}

/* Intro Stats */
.section-intro-stats {
  background: var(--bg-dark);
}

.intro-text {
  font-size: clamp(1.5rem, 4vw, 2.5rem);
  text-align: center;
  max-width: 800px;
  line-height: 1.4;
}

/* Your Year Section */
.section-your-year {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
}

.your-year-title {
  font-size: clamp(1.5rem, 4vw, 2.5rem);
  text-align: center;
  margin-bottom: 3rem;
}

.stats-preview {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
  justify-content: center;
}

.stat-pill {
  background: rgba(255, 255, 255, 0.15);
  padding: 1.5rem 2rem;
  border-radius: 20px;
  text-align: center;
  backdrop-filter: blur(10px);
  min-width: 120px;
}

.stat-pill .stat-value {
  display: block;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 2rem;
  font-weight: 700;
}

.stat-pill .stat-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 0.25rem;
}

/* Stat Card Sections */
.section-trips,
.section-distance,
.section-duration,
.section-operators {
  background: var(--bg-darker);
}

.stat-card {
  text-align: center;
}

.stat-intro {
  font-size: 1.2rem;
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.stat-big-number {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(4rem, 12vw, 8rem);
  font-weight: 700;
  color: var(--accent);
  line-height: 1;
}

.stat-big-number .unit {
  font-size: 0.4em;
  color: var(--text-secondary);
  margin-left: 0.25em;
}

.stat-outro {
  font-size: 1.2rem;
  color: var(--text-secondary);
  margin-top: 1rem;
}

/* Earth Section */
.section-earth {
  background: linear-gradient(180deg, #1a472a 0%, #0d2818 100%);
}

.earth-visual {
  text-align: center;
}

.earth-icon {
  font-size: 6rem;
  margin-bottom: 2rem;
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.stat-highlight {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(3rem, 10vw, 6rem);
  font-weight: 700;
  color: #4ade80;
}

/* Trans-Siberian Section */
.section-transsib {
  background: linear-gradient(180deg, #1e3a5f 0%, #0f1f33 100%);
}

.transsib-visual {
  text-align: center;
}

.train-route {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.city {
  font-size: 1rem;
  color: var(--text-secondary);
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
}

.route-line {
  width: 100px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #60a5fa, transparent);
  position: relative;
}

.route-line::after {
  content: '🚂';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.5rem;
  animation: train 3s ease-in-out infinite;
}

@keyframes train {
  0%, 100% {
    left: 0;
  }
  50% {
    left: 100%;
  }
}

.stat-small {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 1rem;
  max-width: 400px;
}

/* Operator Sections */
.section-fav-operator,
.section-fav-operator-time {
  background: var(--bg-dark);
}

.operator-card {
  text-align: center;
}

.operator-name {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 6vw, 4rem);
  font-weight: 700;
  color: var(--accent);
  margin: 1rem 0;
}

.operator-stat {
  font-size: 1.5rem;
  color: var(--text-secondary);
}

/* Lines Section */
.section-lines {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.lines-card {
  text-align: center;
  max-width: 600px;
}

.lines-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.lines-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 2rem;
}

.lines-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.line-item {
  background: rgba(255, 255, 255, 0.15);
  padding: 1rem 1.5rem;
  border-radius: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.line-name {
  font-weight: 600;
  font-size: 1.2rem;
}

.line-stat {
  color: rgba(255, 255, 255, 0.9);
}

.lines-and {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

/* Speed Section */
.section-speed-intro {
  background: var(--bg-darker);
}

.speed-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(3rem, 10vw, 6rem);
  font-weight: 700;
}

.speed-icon {
  font-size: 4rem;
  margin-top: 1rem;
  animation: wind 1s ease-in-out infinite;
}

@keyframes wind {
  0%, 100% {
    transform: translateX(0);
  }
  50% {
    transform: translateX(20px);
  }
}

/* Trip Cards */
.section-longest,
.section-fastest,
.section-slowest {
  background: linear-gradient(180deg, #f97316 0%, #c2410c 100%);
}

.section-fastest {
  background: linear-gradient(180deg, #22c55e 0%, #15803d 100%);
}

.section-slowest {
  background: linear-gradient(180deg, #8b5cf6 0%, #6d28d9 100%);
}

.trip-card {
  text-align: center;
  max-width: 500px;
}

.trip-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 1rem;
}

.trip-date {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.trip-line {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 2rem;
  font-weight: 700;
  margin: 0.5rem 0;
}

.trip-route {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin: 1rem 0;
  flex-wrap: wrap;
}

.trip-origin,
.trip-dest {
  font-size: 1.1rem;
}

.trip-arrow {
  font-size: 1.5rem;
}

.trip-stat {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 2.5rem;
  font-weight: 700;
  margin-top: 1rem;
}

/* Top Station */
.section-top-station {
  background: linear-gradient(180deg, #059669 0%, #047857 100%);
}

.station-card {
  text-align: center;
}

.station-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.station-name {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 6vw, 3.5rem);
  font-weight: 700;
  margin: 1rem 0;
}

.station-count {
  font-size: 2rem;
  color: rgba(255, 255, 255, 0.9);
}

/* Lonely Stations */
.section-lonely-intro {
  background: var(--bg-darker);
}

.lonely-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700;
  text-align: center;
}

.lonely-subtitle {
  font-size: 1.2rem;
  color: var(--text-secondary);
  margin-top: 1rem;
  text-align: center;
}

.section-lonely {
  background: linear-gradient(180deg, #0d9488 0%, #0f766e 100%);
}

.lonely-card {
  text-align: center;
}

.lonely-count {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2rem;
}

.lonely-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.lonely-station {
  background: rgba(255, 255, 255, 0.15);
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-size: 1.1rem;
}

.lonely-more {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

/* Thanks Section */
.section-thanks {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
}

.thanks-content {
  text-align: center;
}

.thanks-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700;
  margin-bottom: 1rem;
}

.thanks-subtitle {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.9);
  max-width: 500px;
}

.thanks-heart {
  font-size: 4rem;
  margin: 2rem 0;
  animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
}

.thanks-logo {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
  .section {
    padding: 1.5rem;
  }

  .stats-preview {
    flex-direction: column;
    width: 100%;
    max-width: 300px;
  }

  .stat-pill {
    width: 100%;
  }

  .train-route {
    flex-direction: column;
  }

  .route-line {
    width: 2px;
    height: 50px;
  }
}

/* Vatican Section */
.section-vatican {
  background: linear-gradient(180deg, #1e3a5f 0%, #0f1f33 100%);
}

.vatican-visual {
  text-align: center;
}

.vatican-icon {
  position: relative;
  width: 200px;
  height: 200px;
  margin: 0 auto 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle, rgba(255, 215, 0, 0.2) 0%, transparent 70%);
  border-radius: 50%;
}

.vatican-flag {
  font-size: 8rem;
  filter: drop-shadow(0 0 30px rgba(255, 215, 0, 0.5));
  animation: glow 3s ease-in-out infinite;
}

.vatican-train {
  position: absolute;
  font-size: 2.5rem;
  bottom: 10px;
  animation: train-move 4s ease-in-out infinite;
}

@keyframes glow {
  0%, 100% {
    filter: drop-shadow(0 0 30px rgba(255, 215, 0, 0.5));
  }
  50% {
    filter: drop-shadow(0 0 50px rgba(255, 215, 0, 0.8));
  }
}

@keyframes train-move {
  0%, 100% {
    transform: translateX(-30px);
  }
  50% {
    transform: translateX(30px);
  }
}

.section-vatican .stat-highlight {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(4rem, 12vw, 8rem);
  font-weight: 700;
  color: #fbbf24;
  text-shadow: 0 0 40px rgba(251, 191, 36, 0.5);
}

.section-vatican .stat-intro,
.section-vatican .stat-outro {
  color: rgba(255, 255, 255, 0.9);
}

.section-vatican .stat-small {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
  margin-top: 1rem;
}

.delayed-card {
  position: relative;
}

.delay-highlight {
  text-align: center;
  margin-bottom: 1.5rem;
}

.delay-value {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(4rem, 12vw, 7rem);
  font-weight: 700;
  color: #ffd700;
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.6),
  0 0 20px rgba(255, 215, 0, 0.8),
  0 0 30px rgba(255, 215, 0, 1);
  display: block;
  letter-spacing: -1px;
}

.section-delayed {
  background: linear-gradient(180deg, #b91c1c 0%, #7f1d1d 100%);
}

</style>
