function data() {
  function getThemeFromLocalStorage() {
    // if user already changed the theme, use it
    if (window.localStorage.getItem('dark')) {
      return JSON.parse(window.localStorage.getItem('dark'))
    }

    // else return their preferences
    return (
      !!window.matchMedia &&
      window.matchMedia('(prefers-color-scheme: dark)').matches
    )
  }

  function setThemeToLocalStorage(value) {
    window.localStorage.setItem('dark', value)
  }

  return {
    dark: getThemeFromLocalStorage(),
    toggleTheme() {
      this.dark = !this.dark
      setThemeToLocalStorage(this.dark)
    },
    isSideMenuOpen: false,
    toggleSideMenu() {
      this.isSideMenuOpen = !this.isSideMenuOpen
    },
    closeSideMenu() {
      this.isSideMenuOpen = false
    },
    isNotificationsMenuOpen: false,
    toggleNotificationsMenu() {
      this.isNotificationsMenuOpen = !this.isNotificationsMenuOpen
    },
    closeNotificationsMenu() {
      this.isNotificationsMenuOpen = false
    },
    isStripeOpen: false,
    toggleStripeToggle() {
      this.isStripeOpen = !this.isStripeOpen
      if(this.isOnlineOpen = true)
      this.isOnlineOpen = false
    },
    closeStripe() {
      this.isStripeOpen = false
    },
    isOnlineOpen: false,
    toggleOnlineToggle() {
      this.isOnlineOpen = !this.isOnlineOpen
      if(this.isStripeOpen = true)
      this.isStripeOpen = false
    },
    closeOnline() {
      this.isOnlineOpen = false
    },
    onlineValue: 0,
    setisOnlineOpen: function (onlineValue) {
      if(onlineValue == 1)
      this.toggleOnlineToggle
    },
    stripeValue: 0,
    setisStripeOpen: function (stripeValue) {
      if(stripeValue == 1)
      this.toggleStripeToggle
    },
    isProfileMenuOpen: false,
    toggleProfileMenu() {
      this.isProfileMenuOpen = !this.isProfileMenuOpen
    },
    isLangMenuOpen: false,
    toggleLangMenu() {
      this.isLangMenuOpen = !this.isLangMenuOpen
    },
    closeLangMenu() {
      this.isLangMenuOpen = false
    },
    closeProfileMenu() {
      this.isProfileMenuOpen = false
    },
    isPagesMenuOpen: false,
    togglePagesMenu() {
      this.isPagesMenuOpen = !this.isPagesMenuOpen
    },
    // Modal
    isModalOpen: false,
    trapCleanup: null,
    openModal() {
      this.isModalOpen = true
      this.trapCleanup = focusTrap(document.querySelector('#modal'))
    },
    closeModal() {
      this.isModalOpen = false
      this.trapCleanup()
    },
  }
}
