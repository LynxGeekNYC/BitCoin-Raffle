# BitCoin-Raffle

A comprehensive Bitcoin Raffle Ticketing System developed with PHP, MySQL, Bootstrap, and jQuery. This system allows users to create and join raffle games, buy tickets using Bitcoin, and manage their profiles. The admin and game creators have additional control over the game settings, including verifying payments and stopping manually-ended games. The platform supports automatic game creation and ensures security through captcha, Bitcoin payment verification, and user validation.

#Features
- General User Features
- User Registration & Login System:

- Login: Login with email or username.
- Registration: Username, email, password, and Bitcoin withdrawal wallet address.
- Email Verification: Verification link sent to the user's email.
- Password Reset: Reset forgotten passwords.
- Stay Logged In: Option to stay logged in for 5 days.

# Dashboard:

- View Active Raffle Games: Displays active raffle games with details:
- Ticket price (BTC and USD)
- Total pool amount in BTC
- Number of players
- Time left (for timed games)
- Join Raffle Games: Purchase tickets for any available game.
- Referral System: Earn 5% for referring other players.

#Buying Raffle Tickets:

- Buy Tickets: Users can buy tickets by entering the number of tickets they want.
- Bitcoin Payment: Total price shown in both BTC and USD.
- Bitcoin Transaction ID (TXID): Users must provide a valid TXID to verify payment.
- QR Code Generation: A QR code is generated with the exact amount and address for the Bitcoin payment.
- Automatic Conversion: Users can enter the amount in USD or BTC, and it will automatically convert between the two based on the current exchange rate.
- Profile Management:

# Edit Profile: Users can update their username, email, password, and Bitcoin wallet address.
- View Game History: Users can see previous games they participated in, the amount won, and whether the payment was verified.
- Game Features
- Game Creation (for Registered Users):

# Custom Game Name: Users can name their game.
- Ticket Price: Set ticket prices in Bitcoin.
- Player Count & Pool: Minimum number of players required, and the combined pool is displayed.
- End Type: Option to set the game as a timed event (automatic end after a set time) or manual end (stopped by the creator).

# Automatic Game Creation:

A cron job runs every 24 hours, creating new games automatically.

# View Game Details:

- Total Players: Shows all players who participated, their total tickets, and total pool.
- View Payments: Admin and game creators can verify if the player payments were successfully made through Bitcoin.
- Admin & Creator Features

# Admin Panel:

- View All Games: Admins can view all active and completed games, including total players, ticket prices, and pool size.
- Verify Payments: Admins can verify payments using the TXID provided by the player and link to the Bitcoin blockchain for verification.
- Reject Payment: Admins can reject payments and notify the player that the payment was not verified, offering a refund if necessary.
- Stop Game: Manually stop non-timed games. Only the game creator or an admin can stop such games.

# Admin Payout System:

- Manual Payouts: 70% of the pool goes to the winner, 20% to the game creator, and 10% to the admin.
- Admin Dashboard: A section for admins to see who needs to be paid and track whether payments were made.

# Active Users Management:

- Admin Active Users Page: View all users, their total amount paid in Bitcoin, amount won, games created, and referral earnings.
- Referral System Tracking: Admins can track referrals and the payouts owed to referrers.
- Security Features

# Captcha Protection:

- Google reCAPTCHA v3: Protects login and registration forms against bots.

# Secure Transactions:

- Bitcoin payments require a verified TXID.
- Users are not considered active players until their payment is verified.

# Error Handling:

- Proper error handling for all user input and database interactions.
- Fallback mechanisms if the Bitcoin price API fails.
