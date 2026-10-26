export default defineEventHandler(async (event) => {
  await requirePermission(event, 'risk:read')
  return riskRegister()
})
