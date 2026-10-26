export default defineEventHandler(async () => {
  await whenDbReady()
})
