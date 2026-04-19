@props(['title' => 'Terms and Conditions'])

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-white">Terms and Conditions</h1>
    </x-slot>

    <section class="bg-slate-950 py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="prose prose-invert max-w-none space-y-6">

                <!-- 1. Agreement Acceptance -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">1. Acceptance of Terms</h2>
                    <p class="text-slate-300">
                        By accessing and using this cinema booking platform, you accept and agree to be bound by the terms of this agreement. If you do not agree to abide by the above, please do not use this service. We reserve the right to update, change or replace any part of these terms and conditions by posting updates and/or changes to our website.
                    </p>
                </div>

                <!-- 2. Booking Conditions -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">2. Booking and Tickets</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>All bookings are subject to availability and confirmation by the cinema.</li>
                        <li>Ticket prices are confirmed at the time of booking and are subject to applicable taxes, service charges, and internet handling fees.</li>
                        <li>Seats are allocated based on availability and your seating preferences during the booking process.</li>
                        <li>Your booking confirmation will be sent to the email address provided during registration.</li>
                        <li>You must present a valid booking confirmation at the cinema to claim your tickets.</li>
                        <li><strong>Each ticket admits ONE person only.</strong> No ticket can be used by multiple persons.</li>
                        <li>Printed or digital tickets are valid <strong>ONLY</strong> for the specified movie, date, time, and seats.</li>
                        <li><strong>Tickets purchased are non-transferable and cannot be exchanged for other shows, dates, or cinema locations.</strong></li>
                        <li>We recommend that you arrive at least 30 minutes prior to show time at the venue to pick up your physical tickets.</li>
                    </ul>
                </div>

                <!-- 3. Cancellation and Refunds -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">3. Cancellation and Refund Policy - NO REFUNDS OR CANCELLATIONS</h2>
                    <div class="bg-red-900/30 border border-red-700/50 rounded-lg p-4 mb-4">
                        <p class="text-red-300 font-semibold mb-2">⚠️ IMPORTANT - PLEASE READ CAREFULLY</p>
                        <p class="text-slate-200 font-semibold">Once tickets are booked and purchased, NO CANCELLATIONS OR REFUNDS are possible under ANY circumstances.</p>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li><strong class="text-slate-200">No Refunds:</strong> Tickets once booked cannot be refunded. No refund on a purchased ticket is possible, even in case of any rescheduling or postponement of the movie.</li>
                        <li><strong class="text-slate-200">No Cancellations:</strong> Cancellations are not permitted after booking confirmation. If you have booked a ticket, you are committed to that booking.</li>
                        <li><strong class="text-slate-200">No Exchanges:</strong> Purchased tickets cannot be exchanged for other shows, dates, time slots, or cinema locations under any circumstances.</li>
                        <li><strong class="text-slate-200">Non-Attendance:</strong> If you do not attend the show or arrive after the movie has started, no refund will be issued.</li>
                        <li><strong class="text-slate-200">Exception - Government Cancellation:</strong> The only exception is if the event/show is canceled due to lack of government permissions. In such cases, a refund shall be issued to all patrons.</li>
                        <li><strong class="text-slate-200">Internet Handling Fee:</strong> An internet handling fee per ticket is non-refundable regardless of cancellation circumstances.</li>
                        <li><strong class="text-slate-200">Promotional Offers:</strong> Discounts and promotional offers applied to bookings are non-refundable.</li>
                    </ul>
                </div>

                <!-- 4. User Accounts -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">4. User Accounts and Registration</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>You are responsible for maintaining the confidentiality of your account information and password.</li>
                        <li>You agree to accept responsibility for all activities that occur under your account.</li>
                        <li>You must provide accurate, complete, and current information during the registration process.</li>
                        <li>You are prohibited from using false or misleading information.</li>
                        <li>We reserve the right to suspend or terminate your account if any information provided is found to be false or misleading.</li>
                        <li>You are solely responsible for safeguarding your login credentials.</li>
                    </ul>
                </div>

                <!-- 5. Payment -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">5. Payment Terms</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>Payment must be completed before your booking is confirmed. NO booking is confirmed until full payment is received.</li>
                        <li>We accept all major credit cards, debit cards, and other payment methods as displayed on the platform.</li>
                        <li>You authorize us to charge your payment method for the total amount of your booking, including taxes, service charges, and internet handling fees.</li>
                        <li>All charges are final and non-refundable. By completing payment, you acknowledge and accept the no-refund policy.</li>
                        <li>Price and availability information is provided in real-time but is subject to change without notice.</li>
                        <li>All prices are displayed in the currency specified by your location or as selected on the booking page.</li>
                    </ul>
                </div>

                <!-- 6. Disclaimer of Warranties -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">6. Disclaimer of Warranties</h2>
                    <p class="text-slate-300 mb-3">
                        This website is provided on an "as-is" and "as-available" basis. We make no warranties, expressed or implied, regarding the website or the information, content, or materials included on the website. We disclaim all warranties to the fullest extent permitted by law, including but not limited to:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>Implied warranties of merchantability and fitness for a particular purpose</li>
                        <li>Warranties against infringement of third-party intellectual property rights</li>
                        <li>Warranties regarding the accuracy, completeness, or currency of information</li>
                    </ul>
                </div>

                <!-- 7. Limitation of Liability -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">7. Limitation of Liability</h2>
                    <p class="text-slate-300">
                        To the fullest extent permitted by law, in no event shall our cinema or its owners, operators, employees, or agents be liable for any indirect, incidental, special, consequential, or punitive damages, including lost profits, lost revenue, lost data, or other damages, arising out of or related to your use of this website or your bookings, even if we have been advised of the possibility of such damages.
                    </p>
                </div>

                <!-- 8. Code of Conduct -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">8. Code of Conduct</h2>
                    <p class="text-slate-300 mb-3">While using this platform and attending movies at our cinema, you agree to:</p>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>Comply with all applicable laws and regulations</li>
                        <li>Refrain from engaging in harassment, abuse, or threatening behavior</li>
                        <li>Not engage in any form of discrimination based on race, religion, gender, ethnicity, or other protected characteristics</li>
                        <li>Respect the privacy and personal rights of other users and cinema patrons</li>
                        <li>Not record or photograph films or performances without prior written consent</li>
                        <li>Follow all cinema policies and staff instructions during your visit</li>
                    </ul>
                </div>

                <!-- 9. Intellectual Property -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">9. Intellectual Property Rights</h2>
                    <p class="text-slate-300">
                        All content on this website, including text, graphics, logos, images, and software, is the property of our cinema or its content suppliers and is protected by international copyright laws. You may not reproduce, distribute, or transmit any content without our prior written consent.
                    </p>
                </div>

                <!-- 10. Privacy Policy -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">10. Privacy and Data Protection</h2>
                    <p class="text-slate-300">
                        Your use of this platform is subject to our Privacy Policy. By using this website and making bookings, you consent to the collection and use of your personal information as outlined in our Privacy Policy. We are committed to protecting your personal data and maintaining its confidentiality in accordance with applicable data protection laws.
                    </p>
                </div>

                <!-- 11. Third-Party Links -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">11. Third-Party Links and Content</h2>
                    <p class="text-slate-300">
                        Our website may contain links to third-party websites and services. We are not responsible for the content, accuracy, or practices of these external sites. Your use of third-party websites and services is governed by their respective terms and conditions and privacy policies.
                    </p>
                </div>

                <!-- 12. Prohibited Activities -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">12. Prohibited Activities</h2>
                    <p class="text-slate-300 mb-3">You agree not to:</p>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>Use the website for any unlawful or fraudulent purposes</li>
                        <li>Attempt to gain unauthorized access to the website or its systems</li>
                        <li>Interfere with the normal operation of the website</li>
                        <li>Engage in spam, phishing, or other malicious activities</li>
                        <li><strong>Resell, redistribute, or attempt to resell tickets without authorization</strong> - Unlawful resale or attempted unlawful resale of tickets will result in seizure and cancellation of that ticket without refund, and legal action will be taken against such parties</li>
                        <li>Use automated tools or bots to access or manipulate the booking system</li>
                    </ul>
                </div>

                <!-- 12A. Ticket Disclaimer and Important Terms -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">12A. Important Ticket Disclaimer</h2>
                    <div class="bg-red-900/20 border border-red-700/40 rounded-lg p-4 mb-4 space-y-3">
                        <p class="text-slate-200"><span class="font-semibold text-red-300">✓ Ticket Validity:</span> Each ticket is valid ONLY for the particular show, date, cinema, and seats specified. No exchanges or transfers are allowed.</p>
                        <p class="text-slate-200"><span class="font-semibold text-red-300">✓ One Person Per Ticket:</span> Each ticket admits one person only. Tickets cannot be shared or used by multiple individuals.</p>
                        <p class="text-slate-200"><span class="font-semibold text-red-300">✓ Arrival Time:</span> We recommend arriving at least 30 minutes prior to the show time to collect your tickets. Tickets unclaimed after the show starts will be deemed forfeited.</p>
                        <p class="text-slate-200"><span class="font-semibold text-red-300">✓ Government Permissions:</span> Shows are subject to government permissions. If permissions are not granted and the show is canceled, refunds will be issued. Otherwise, no refunds are applicable.</p>
                        <p class="text-slate-200"><span class="font-semibold text-red-300">✓ Ticket Resale Prohibition:</span> Unlawful resale (or attempted resale) of tickets will lead to seizure and cancellation of the ticket without refund. Legal action will be taken against violators.</p>
                        <p class="text-slate-200"><span class="font-semibold text-red-300\">✓ Internet Handling Fees:</span> Internet handling fees per ticket are charged and are non-refundable.</p>
                    </div>
                </div>

                <!-- 12B. Venue Rules and Conduct -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">12B. Venue Rules and Patron Conduct</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li><strong>Security Checks:</strong> The cinema reserves the right to perform security checks on all patrons at the entry point for safety and security reasons.</li>
                        <li><strong>Prohibited Substances:</strong> Persons under the influence of alcohol or any intoxicating substances will not be allowed inside the venue.</li>
                        <li><strong>Respectful Conduct:</strong> Any disrespect, harassment, or harm to actors, crew members, or other patrons will not be tolerated and may result in immediate eviction without refund.</li>
                        <li><strong>No Recording:</strong> Recording, photography, or any form of capturing the movie or performance without prior written consent is strictly prohibited.</li>
                        <li><strong>Follow Staff Instructions:</strong> All patrons must follow instructions given by cinema staff members at all times.</li>
                    </ul>
                </div>

                <!-- 12C. Liability Waiver -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">12C. Liability Waiver</h2>
                    <p class="text-slate-300 mb-3">
                        <strong>The cinema, its organizers, agents, officers, and employees shall NOT be responsible for:</strong>
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-slate-300">
                        <li>Any injury, damage, or harm to patrons at or during the event</li>
                        <li>Theft, loss, or damage to personal belongings at the venue</li>
                        <li>Any costs or expenses incurred as a result of attending the event</li>
                        <li>Cancellations or postponements of shows (except where refunds apply)</li>
                        <li>Any form of inconvenience or dissatisfaction during your cinema experience</li>
                    </ul>
                    <p class="text-slate-300 mt-3">
                        By purchasing tickets and attending the show, you assume all risks and release the cinema from any liability for the items listed above.
                    </p>
                </div>

                <!-- 13. Changes to Service -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">13. Changes to Service</h2>
                    <p class="text-slate-300">
                        We reserve the right to modify, suspend, or discontinue the booking service at any time with or without notice. We shall not be liable to you or any third party for any modification, suspension, or discontinuation of the service.
                    </p>
                </div>

                <!-- 14. Governing Law -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">14. Governing Law and Jurisdiction</h2>
                    <p class="text-slate-300">
                        These terms and conditions are governed by and construed in accordance with the laws of the jurisdiction in which our cinema is located, and you irrevocably submit to the exclusive jurisdiction of the courts and tribunals in that location.
                    </p>
                </div>

                <!-- 15. Contact Information -->
                <div>
                    <h2 class="text-2xl font-bold text-white mb-4">15. Contact Us</h2>
                    <p class="text-slate-300 mb-3">
                        If you have any questions or concerns regarding these Terms and Conditions, please contact us at:
                    </p>
                    <div class="bg-slate-900 p-4 rounded-lg">
                        <p class="text-slate-200"><strong>Email:</strong> support@cinemaplatform.com</p>
                        <p class="text-slate-200"><strong>Phone:</strong> +1 (800) CINEMA-1</p>
                        <p class="text-slate-200"><strong>Address:</strong> Cinema Complex, Main Street</p>
                    </div>
                </div>

                <!-- Last Updated -->
                <div class="border-t border-slate-700 pt-6">
                    <p class="text-sm text-slate-500">
                        <strong>Last Updated:</strong> {{ now()->format('F d, Y') }}
                    </p>
                </div>

            </div>
        </div>
    </section>
</x-app-layout>
