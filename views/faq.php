<?php
$faqCategories = [
    'Orders & shopping' => [
        [
            'question' => 'How can I place an order?',
            'answer' => 'Choose a fragrance and size, add it to your bag, then complete checkout with your delivery details and an available payment method.',
        ],
        [
            'question' => 'Can I modify or cancel my order?',
            'answer' => 'Customers may cancel pending or confirmed orders within the cancellation window configured by the store. Contact us promptly with your order number; changes cannot be guaranteed once fulfilment has moved forward.',
            'link' => ['url' => app_url('contact'), 'label' => 'Contact the house'],
        ],
        [
            'question' => 'How can I track my order?',
            'answer' => 'Use your order number and the mobile number entered at checkout on our tracking page.',
            'link' => ['url' => app_url('track'), 'label' => 'Track an order'],
        ],
    ],
    'Products & fragrance' => [
        [
            'question' => 'How do I choose the right fragrance?',
            'answer' => 'Read the fragrance description and notes on each product page, or start with our scent finder to explore by mood.',
            'link' => ['url' => app_url('mood'), 'label' => 'Find your feeling'],
        ],
        [
            'question' => 'Are your fragrances authentic?',
            'answer' => 'Each listing identifies its fragrance house and product details. For questions about a particular fragrance or batch, please contact us before ordering.',
            'link' => ['url' => app_url('contact'), 'label' => 'Ask us a question'],
        ],
        [
            'question' => 'What fragrance sizes are available?',
            'answer' => 'Available sizes and their current prices are shown on the product page. Options can vary by fragrance and stock.',
        ],
    ],
    'Payment' => [
        [
            'question' => 'Which payment methods are accepted?',
            'answer' => 'Payment options depend on what is currently enabled and are shown during checkout. These may include bKash, Nagad, and cash on delivery.',
        ],
        [
            'question' => 'How does bKash or Nagad payment verification work?',
            'answer' => 'After sending payment, enter the transaction ID at checkout. Mobile payments remain pending until the store team verifies them.',
        ],
    ],
    'Delivery & returns' => [
        [
            'question' => 'Do you deliver across Bangladesh?',
            'answer' => 'Delivery is available only in the active zones listed at checkout. See Delivery & Returns for the current zone names, fees, and estimates.',
            'link' => ['url' => app_url('delivery'), 'label' => 'View delivery details'],
        ],
        [
            'question' => 'How long does delivery take?',
            'answer' => 'The current estimated window is listed for each active delivery zone and is shown again at checkout. Actual delivery time can vary.',
            'link' => ['url' => app_url('delivery'), 'label' => 'See current estimates'],
        ],
        [
            'question' => 'What should I do if my order arrives damaged?',
            'answer' => 'Please contact us as soon as possible with your order number and clear photos of the item and packaging so we can review what happened.',
            'link' => ['url' => app_url('contact'), 'label' => 'Contact the house'],
        ],
        [
            'question' => 'What is your return policy?',
            'answer' => 'A return-eligibility policy is not currently published, and refunds are not automated through this website. Contact customer care before sending an item back; do not assume a return or refund is approved until the store responds.',
            'link' => ['url' => app_url('contact'), 'label' => 'Contact customer care'],
        ],
    ],
];
?>
<section class="page-hero page-hero-small faq-hero">
    <span class="eyebrow">A little clarity</span>
    <h1>Frequently asked questions.</h1>
    <p>Helpful details for finding your fragrance and receiving it with care.</p>
</section>
<section class="section faq-section" aria-label="Frequently asked questions">
    <?php foreach ($faqCategories as $category => $questions): ?>
        <section class="faq-category" aria-labelledby="faq-<?= h(strtolower(str_replace([' ', '&'], ['-', 'and'], $category))) ?>">
            <h2 id="faq-<?= h(strtolower(str_replace([' ', '&'], ['-', 'and'], $category))) ?>"><?= h($category) ?></h2>
            <div class="faq-list">
                <?php foreach ($questions as $question): ?>
                    <details class="faq-item">
                        <summary>
                            <span><?= h($question['question']) ?></span>
                            <svg class="faq-chevron" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <p><?= h($question['answer']) ?><?php if (!empty($question['link'])): ?> <a href="<?= h($question['link']['url']) ?>"><?= h($question['link']['label']) ?></a>.<?php endif; ?></p>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</section>