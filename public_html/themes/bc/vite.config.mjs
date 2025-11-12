import { defineConfig } from 'vite';

const modulesMap = {
    checkout: '../../module/booking/scss/checkout.scss',
    user: '../../module/user/scss/user.scss',
    profile: '../../module/user/scss/profile.scss',
    tour: '../../module/tour/scss/tour.scss',
    space: '../../module/space/scss/space.scss',
    flight: '../../module/flight/scss/flight.scss',
    hotel: '../../module/hotel/scss/hotel.scss',
    news: '../../module/news/scss/news.scss',
    media: '../../module/media/scss/browser.scss',
    location: '../../module/location/scss/location.scss',
    car: '../../module/car/scss/car.scss',
    event: '../../module/event/scss/event.scss',
    social: '../../module/social/scss/social.scss',
    boat: '../../module/boat/scss/boat.scss',
    support: '../../module/support/scss/support.scss',
}

export default defineConfig({
  build: {
    outDir: 'dist/frontend',
    rollupOptions: {
      input: {
        app: 'sass/app.scss',
        contact: 'sass/contact.scss',
        rtl: 'sass/rtl.scss',
        notification: 'sass/notification.scss',
        // ----------------------------------------------------------------------------------------------------
        //Booking
        ...modulesMap,
      },
      output: {
        assetFileNames: (assetInfo) => {
            const fileNameToTest = assetInfo.originalFileNames[0];
            if(fileNameToTest){
                const match = fileNameToTest.match(/module\/([^/]+)/);
                if(match?.[1]){
                    return 'module/' + match[1] + '/css/[name].[ext]';
                }
            }
          return 'css/[name].[ext]';
        },
      },
    },
  },
});
