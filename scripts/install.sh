#!/usr/bin/env bash

set -e
set -o pipefail

ASCII_ART="\



  _______                          _ _ _
 |__   __|                        | | (_)
    | |_ __ __ _  _____      _____| | |_ _ __   __ _
    | | '__/ _\` \|/ _ \ \ /\ / / _ \ | | | '_ \ / _\` |
    | | | | (_| |  __/\ V  V /  __/ | | | | | | (_| |
    |_|_|  \__,_|\___| \_/\_/ \___|_|_|_|_| |_|\__, |
                                                __/ |
                                               |___/


                                               "

USER=${1:-www-data} # Default user is www-data
REPO_ROOT=$(git rev-parse --show-toplevel)

# Colors for fancy output
GREEN="\033[1;32m"
CYAN="\033[1;36m"
YELLOW="\033[1;33m"
RED="\033[1;31m"
RESET="\033[0m"
TRWL_RED="\033[38;2;255;0;0m"

ask_to_install_service() {
  service_name="$1"

  source_file="${REPO_ROOT}/scripts/services/${service_name}.service"
  destination_file="/etc/systemd/system/${service_name}.service"

  if [ -f "$destination_file" ]; then
    echo -e "${YELLOW}Service ${service_name} is already installed.${RESET}"
    return 1
  fi

  echo -e "${YELLOW}Service ${service_name} is not installed.${RESET}"
  echo -e "${YELLOW}Do you want to install? Sudo may ask for your password.${RESET}"
  echo -e "${YELLOW}[Y/n] (default: yes): ${RESET}"

  read -r answer

  case "$answer" in
    y|Y|yes|Yes|"")
      echo -e "${YELLOW}Installing ${service_name} service to ${destination_file}...${RESET}"
      sudo cp "$source_file" "$destination_file"

      sudo sed -i "s|REPLACE_ROOT_PATH|${REPO_ROOT}|g" "$destination_file"
      sudo sed -i "s|REPLACE_USER|${USER}|g" "$destination_file"

      sudo systemctl enable "$service_name"
      sudo systemctl start "$service_name"

      echo -e "${GREEN}${service_name} service installed successfully!${RESET}"
      return 0
      ;;
    n|N|no|No)
      echo -e "${YELLOW}Skipping ${service_name} service installation...${RESET}"
      return 1
      ;;
    *)
      echo -e "${RED}Invalid answer. Please answer with 'y' or 'n'.${RESET}"
      ask_to_install_service "$service_name"
      ;;
  esac
}

welcome_message() {
  echo -e "${TRWL_RED}${ASCII_ART}${RESET}"
  echo -e "${CYAN}This script will install / update Träwelling to the latest develop version.${RESET}\n"
  echo -e "${YELLOW}Running as: ${GREEN}${USER}${RESET}\n"

  echo -e "${YELLOW}Continue installation in ${GREEN}${REPO_ROOT}${YELLOW} directory?\n[Y/n] (default: yes): ${RESET}"
  read -r answer

  case "$answer" in
    y|Y|yes|Yes|"")
      echo -e "${GREEN}Okay, let's go!${RESET}"
      cd "$REPO_ROOT"
      echo -e "${YELLOW}Starting installation at $(date --iso-8601=seconds)${RESET}\n\n\n"
      ;;
    n|N|no|No)
      echo -e "${YELLOW}Bye.${RESET}"
      exit 1
      ;;
    *)
      echo -e "${RED}Invalid answer. Please answer with 'y' or 'n'.${RESET}"
      welcome_message
      ;;
  esac
}

check_dependencies() {
  if [ "$USER" != "$(whoami)" ]; then
    echo -e "${RED}Please run this script as ${USER} user.${RESET}"
    exit 1
  fi

  if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP and try again.${RESET}"
    exit 1
  fi

  if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed. Please install Composer and try again.${RESET}"
    exit 1
  fi

  if ! command -v npm &> /dev/null; then
    echo -e "${RED}NPM is not installed. Please install NPM and try again.${RESET}"
    exit 1
  fi

  if ! command -v git &> /dev/null; then
    echo -e "${RED}Git is not installed. Please install Git and try again.${RESET}"
    exit 1
  fi
}

activate_maintenance_mode() {
  echo -e "${YELLOW}Enabling maintenance mode...${RESET}"
  php artisan down
}

pull_latest_changes() {
  echo -e "${YELLOW}Pulling latest changes...${RESET}"
  git pull
}

install_composer_dependencies() {
  echo -e "${YELLOW}Installing composer dependencies...${RESET}"
  composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
}

install_npm_dependencies() {
  echo -e "${YELLOW}Installing npm dependencies...${RESET}"
  npm ci --no-audit --no-progress
  npm run build
}

run_migrations() {
  echo -e "${YELLOW}Running migrations...${RESET}"
  php artisan migrate --force
}

finish_application() {
  echo -e "${YELLOW}Optimizing application...${RESET}"
  php artisan optimize

  echo -e "${YELLOW}Seeding constants to database...${RESET}"
  php artisan db:seed --class=Database\\Seeders\\Constants\\PermissionSeeder --force

  echo -e "${YELLOW}Disabling maintenance mode...${RESET}"
  php artisan up
}

restart_services() {
  if ! command -v systemctl &> /dev/null; then
    echo -e "${RED}Systemd is not available on this system. Skipping service restart.${RESET}"
    return
  fi

  echo -e "\n\n${CYAN}Restarting services...${RESET}"
  echo -e "${YELLOW}You may be asked to enter your password here if you're not running as root.${RESET}\n\n"

  services=(
    "traewelling-queue"
    "traewelling-queue-webhook"
    "traewelling-queue-export"
  )

  for service in "${services[@]}"; do
    service_path="/etc/systemd/system/${service}.service"
    if [ -f "$service_path" ]; then
      echo -e "${YELLOW}Restarting ${service} service...${RESET}"
      sudo systemctl restart "$service"
      echo -e "${GREEN}${service} service restarted successfully!${RESET}"
    else
      ask_to_install_service "$service"
    fi
  done
}

run_installation() {
  welcome_message
  check_dependencies | sed "s/^/[DependencyCheck] /"
  activate_maintenance_mode | sed "s/^/[PreInstall] /"
  pull_latest_changes | sed "s/^/[git] /"
  install_composer_dependencies | sed "s/^/[composer] /"
  install_npm_dependencies | sed "s/^/[npm] /"
  run_migrations | sed "s/^/[Migration] /"
  finish_application | sed "s/^/[PostInstall] /"
  restart_services | sed "s/^/[ServiceManager] /"

  echo -e "\n\n${GREEN}Application updated successfully at $(date --iso-8601=seconds)!${RESET}"
}

run_installation 2>&1 | tee -a "${REPO_ROOT}/storage/logs/install-$(date --iso-8601=seconds).log"
