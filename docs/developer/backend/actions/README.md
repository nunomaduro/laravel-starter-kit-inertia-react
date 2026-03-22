# Actions

Actions are single-purpose classes that encapsulate business logic. They live in `app/Actions/` and follow a consistent pattern.

## Pattern

All Actions:
- Have a single `handle()` method
- Are `final readonly` classes
- Accept dependencies via constructor
- Return typed values

## Available Actions

| Action | Documented |
|------|------|
| [CreateUser](./CreateUser.md) | Yes |
| [CreateUserEmailResetNotification](./CreateUserEmailResetNotification.md) | Yes |
| [CreateUserEmailVerificationNotification](./CreateUserEmailVerificationNotification.md) | Yes |
| [CreateUserPassword](./CreateUserPassword.md) | Yes |
| [DeleteUser](./DeleteUser.md) | Yes |
| [UpdateUser](./UpdateUser.md) | Yes |
| [UpdateUserPassword](./UpdateUserPassword.md) | Yes |
| [LoggingEnableTwoFactorAuthentication](./LoggingEnableTwoFactorAuthentication.md) | Yes |
| [LoggingDisableTwoFactorAuthentication](./LoggingDisableTwoFactorAuthentication.md) | Yes |
| [LoggingConfirmTwoFactorAuthentication](./LoggingConfirmTwoFactorAuthentication.md) | Yes |
| [LoggingGenerateNewRecoveryCodes](./LoggingGenerateNewRecoveryCodes.md) | Yes |
| [StoreContactSubmission](./StoreContactSubmission.md) | Yes |
| [CompleteOnboardingAction](./CompleteOnboardingAction.md) | Yes |
| [RateHelpArticleAction](./RateHelpArticleAction.md) | Yes |
| [AcceptOrganizationInvitationAction](./acceptorganizationinvitationaction.md) | Yes |
| [CreateOrganizationAction](./createorganizationaction.md) | Yes |
| [CreatePersonalOrganizationForUserAction](./createpersonalorganizationforuseraction.md) | Yes |
| [InviteToOrganizationAction](./invitetoorganizationaction.md) | Yes |
| [RemoveOrganizationMemberAction](./removeorganizationmemberaction.md) | Yes |
| [SwitchOrganizationAction](./switchorganizationaction.md) | Yes |
| [TransferOrganizationOwnershipAction](./transferorganizationownershipaction.md) | Yes |
| [GetRequiredTermsVersionsForUser](./GetRequiredTermsVersionsForUser.md) | Yes |
| [RecordTermsAcceptance](./RecordTermsAcceptance.md) | Yes |
| [StoreEnterpriseInquiryAction](./StoreEnterpriseInquiryAction.md) | Yes |
| [BulkSoftDeleteUsers](./BulkSoftDeleteUsers.md) | Yes |
| [DuplicateUser](./DuplicateUser.md) | Yes |
| [UpdateUserThemeMode](docs/developer/backend/actions/UpdateUserThemeMode.md) | Yes |
| [SuggestThemeFromLogo](docs/developer/backend/actions/SuggestThemeFromLogo.md) | Yes |
| [RecordAuditLog](docs/developer/backend/actions/RecordAuditLog.md) | Yes |
| [VerifyCustomDomain](docs/developer/backend/actions/VerifyCustomDomain.md) | Yes |
| [FindOrCreateSocialUser](./FindOrCreateSocialUser.md) | Yes |
| [BatchUpdateUsersAction](./BatchUpdateUsersAction.md) | Yes |
| [BuildLaravelDailyInvoice](./Billing/BuildLaravelDailyInvoice.md) | Yes |
| [FindSimilarContent](./findsimilarcontent.md) | Yes |
| [GenerateOgImage](./generateogimage.md) | Yes |


