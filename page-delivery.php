<?php
/**
 * Template Name: Доставка та оплата
 */

defined( 'ABSPATH' ) || exit;

$pid = get_the_ID();

$phone          = get_post_meta( $pid, '_del_phone', true )          ?: '+38 063 253 26 96';
$phone_href     = 'tel:+' . preg_replace( '/[^0-9]/', '', $phone );
$order_hours    = get_post_meta( $pid, '_del_order_hours', true )    ?: '9:00 – 18:30';
$delivery_hours = get_post_meta( $pid, '_del_delivery_hours', true ) ?: '9:30 – 19:30';
$address        = get_post_meta( $pid, '_del_address', true )        ?: 'вул. Воскресенська, 41';
$maps_url       = get_post_meta( $pid, '_del_maps_url', true )       ?: 'https://www.google.com/maps/d/u/0/embed?mid=1zXrpRXH2YNa7LDv6pq-footc-xzq8Ic&ehbc=2E312F&noprof=1';
$green_free     = (int) ( get_post_meta( $pid, '_del_green_free', true ) ?: 600 );
$green_cost     = (int) ( get_post_meta( $pid, '_del_green_cost', true ) ?: 100 );
$yellow_free    = (int) ( get_post_meta( $pid, '_del_yellow_free', true ) ?: 800 );
$yellow_cost    = (int) ( get_post_meta( $pid, '_del_yellow_cost', true ) ?: 150 );

$faq_raw = get_post_meta( $pid, '_del_faq', true );
$faq     = $faq_raw ? json_decode( $faq_raw, true ) : [
    [ 'q' => 'Як довго їде замовлення?',                    'a' => 'Зазвичай ми доставляємо протягом 2х годин. Але бувають виключення, залежно від завантаження кухні. Точний час вам повідомить оператор після підтвердження замовлення. Доставляємо щодня з 9:30 до 19:30.' ],
    [ 'q' => 'Чи можна замовити з вечора на завтра?',      'a' => 'Так, можна замовити заздалегідь — для цього зателефонуйте або вкажіть бажану дату та час доставки при оформленні онлайн.' ],
    [ 'q' => 'Як зрозуміти, в якій зоні моя адреса?',     'a' => 'Подивіться на карту вище — зелена та жовта зони позначені кольорами. Якщо адреса на межі або не відображається — зателефонуйте, оператор уточнить.' ],
    [ 'q' => 'Як оплатити замовлення?',                     'a' => 'Двома способами: готівкою кур\'єру при отриманні, або онлайн карткою при оформленні замовлення в кошику.' ],
    [ 'q' => 'Що робити, якщо моєї адреси немає на карті?', 'a' => 'Зателефонуйте нам — обговоримо індивідуально. Часто ми можемо доставити навіть у зони, що не позначені на карті, за погодженою вартістю.' ],
    [ 'q' => 'Як швидко ви приймаєте онлайн-замовлення?',  'a' => 'Зазвичай оператор передзвонює протягом 5–15 хвилин для підтвердження. Якщо замовлення оформлене після 18:30 — зв\'яжемось вранці наступного дня.' ],
];

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

get_header();
?>

<main class="dlv-wrap">

    <!-- PAGE HEAD -->
    <section class="dlv-head">
        <div class="dlv-head__inner">
            <nav class="cart-breadcrumbs" aria-label="Breadcrumbs">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
                <span aria-hidden="true">›</span>
                <span><?php the_title(); ?></span>
            </nav>
            <h1 class="dlv-head__title"><?php the_title(); ?></h1>
            <p class="dlv-head__lede">Привеземо свіжі пироги додому або в офіс по Дніпру. Приймаємо замовлення щодня з <?php echo esc_html( $order_hours ); ?>.</p>
        </div>
    </section>

    <div class="dlv-content">

        <!-- HIGHLIGHT -->
        <div class="dlv-highlight">
            <div class="dlv-highlight__icon">🚲</div>
            <div class="dlv-highlight__text">
                <div class="dlv-highlight__title">Безкоштовна доставка по Дніпру</div>
                <div class="dlv-highlight__desc">Від <?php echo esc_html( $green_free ); ?> грн у зеленій зоні або від <?php echo esc_html( $yellow_free ); ?> грн у жовтій</div>
            </div>
            <div class="dlv-highlight__amount">
                <?php echo esc_html( $green_free ); ?> грн
                <small>від</small>
            </div>
        </div>

        <!-- HOURS -->
        <div class="dlv-info-grid">
            <div class="dlv-info-card">
                <div class="dlv-info-card__head">
                    <div class="dlv-info-card__icon">📞</div>
                    <div class="dlv-info-card__title">Приймаємо замовлення</div>
                </div>
                <div class="dlv-time-row">
                    <span class="dlv-time-row__label">Щодня</span>
                    <span class="dlv-time-row__value"><?php echo esc_html( $order_hours ); ?></span>
                </div>
                <div class="dlv-time-row">
                    <span class="dlv-time-row__label">Телефон</span>
                    <span class="dlv-time-row__value" style="font-size:15px;"><?php echo esc_html( $phone ); ?></span>
                </div>
            </div>
            <div class="dlv-info-card">
                <div class="dlv-info-card__head">
                    <div class="dlv-info-card__icon">🚐</div>
                    <div class="dlv-info-card__title">Доставляємо</div>
                </div>
                <div class="dlv-time-row">
                    <span class="dlv-time-row__label">Щодня</span>
                    <span class="dlv-time-row__value"><?php echo esc_html( $delivery_hours ); ?></span>
                </div>
                <div class="dlv-time-row">
                    <span class="dlv-time-row__label">Місто</span>
                    <span class="dlv-time-row__value" style="font-size:15px;">Дніпро</span>
                </div>
            </div>
        </div>

        <!-- DELIVERY ZONES -->
        <div class="dlv-zones">
            <div class="dlv-zones__head">
                <h2>Вартість доставки</h2>
                <p>Точна сума уточнюється оператором</p>
            </div>
            <div class="dlv-zones__list">
                <div class="dlv-zone dlv-zone--green">
                    <div class="dlv-zone__badge dlv-zone__badge--green">Зелена зона</div>
                    <div class="dlv-zone__rows">
                        <div class="dlv-zone__row">
                            <div class="dlv-zone__row-label">Від <?php echo esc_html( $green_free ); ?> грн</div>
                            <div class="dlv-zone__row-value dlv-zone__free-pill">Безкоштовно</div>
                        </div>
                        <div class="dlv-zone__row">
                            <div class="dlv-zone__row-label">До <?php echo esc_html( $green_free ); ?> грн</div>
                            <div class="dlv-zone__row-value">+<?php echo esc_html( $green_cost ); ?> грн</div>
                        </div>
                    </div>
                </div>
                <div class="dlv-zone dlv-zone--yellow">
                    <div class="dlv-zone__badge dlv-zone__badge--yellow">Жовта зона</div>
                    <div class="dlv-zone__rows">
                        <div class="dlv-zone__row">
                            <div class="dlv-zone__row-label">Від <?php echo esc_html( $yellow_free ); ?> грн</div>
                            <div class="dlv-zone__row-value dlv-zone__free-pill">Безкоштовно</div>
                        </div>
                        <div class="dlv-zone__row">
                            <div class="dlv-zone__row-label">До <?php echo esc_html( $yellow_free ); ?> грн</div>
                            <div class="dlv-zone__row-value">+<?php echo esc_html( $yellow_cost ); ?> грн</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dlv-zones__hint">
                <span aria-hidden="true">💡</span>
                Подивіться на карті нижче, до якої зони належить ваша адреса
            </div>
        </div>

        <!-- MAP -->
        <div class="dlv-map-section">
            <div class="dlv-section-eyebrow">Зони доставки</div>
            <h2 class="dlv-section-title">Куди ми доставляємо</h2>
            <p class="dlv-section-desc">Перевірте, чи ваша адреса входить у зону доставки</p>
            <div class="dlv-map-wrap">
                <iframe src="<?php echo esc_url( $maps_url ); ?>" loading="lazy" title="Карта зон доставки"></iframe>
                <div class="dlv-map-card">
                    <div class="dlv-map-card__icon">📍</div>
                    <div>
                        <div class="dlv-map-card__label">Наша пекарня</div>
                        <div class="dlv-map-card__addr"><?php echo esc_html( $address ); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENT -->
        <div class="dlv-payment">
            <div class="dlv-section-eyebrow">Способи оплати</div>
            <h2 class="dlv-section-title">Як оплатити</h2>
            <p class="dlv-section-desc">Оплачуйте зручним для вас способом — готівкою при отриманні або карткою онлайн</p>
            <div class="dlv-payment__grid">
                <div class="dlv-payment__method">
                    <div class="dlv-payment__icon">💵</div>
                    <div class="dlv-payment__name">При отриманні</div>
                    <div class="dlv-payment__desc">Готівкою або карткою кур'єру, коли замовлення приїде до вас</div>
                </div>
                <div class="dlv-payment__method">
                    <div class="dlv-payment__icon">🔒</div>
                    <div class="dlv-payment__name">Онлайн на сайті</div>
                    <div class="dlv-payment__desc">Оплата карткою прямо при оформленні замовлення в кошику</div>
                </div>
            </div>
        </div>

        <!-- PICKUP -->
        <div class="dlv-pickup">
            <div class="dlv-pickup__content">
                <div class="dlv-pickup__eyebrow">Безкоштовно</div>
                <h2>Самовивіз</h2>
                <p>Заберіть замовлення особисто та зекономте на доставці. Пироги тільки-но з печі чекають на вас.</p>
                <div class="dlv-pickup__addr-block">
                    <div class="dlv-pickup__addr-icon">📍</div>
                    <div>
                        <div class="dlv-pickup__addr-label">Адреса пекарні</div>
                        <div class="dlv-pickup__addr-value"><?php echo esc_html( $address ); ?></div>
                    </div>
                </div>
                <a href="https://maps.google.com/?q=<?php echo urlencode( $address . ', Дніпро' ); ?>" target="_blank" rel="noopener noreferrer" class="dlv-pickup__btn">
                    Прокласти маршрут →
                </a>
            </div>
            <div class="dlv-pickup__visual" aria-hidden="true">
                <div class="dlv-pickup__emoji-grid">
                    <div class="dlv-pickup__emoji">🥧</div>
                    <div class="dlv-pickup__emoji">🍎</div>
                    <div class="dlv-pickup__emoji">🍒</div>
                    <div class="dlv-pickup__emoji">🥖</div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="dlv-faq">
            <div class="dlv-section-eyebrow">Корисно знати</div>
            <h2 class="dlv-section-title">Часті запитання</h2>
            <div class="dlv-faq__list">
                <?php foreach ( $faq as $i => $item ) :
                    if ( empty( $item['q'] ) ) continue; ?>
                <details class="dlv-faq__item"<?php echo $i === 0 ? ' open' : ''; ?>>
                    <summary><?php echo esc_html( $item['q'] ); ?></summary>
                    <div class="dlv-faq__answer"><?php echo wp_kses_post( $item['a'] ); ?></div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA -->
        <div class="dlv-cta">
            <h2>Готові замовити?</h2>
            <p>Свіжі пироги вже чекають на вашу адресу</p>
            <div class="dlv-cta__buttons">
                <a href="<?php echo esc_url( $shop_url ); ?>" class="dlv-cta__btn dlv-cta__btn--primary">Перейти до каталогу →</a>
                <a href="<?php echo esc_attr( $phone_href ); ?>" class="dlv-cta__btn dlv-cta__btn--secondary">📞 Зателефонувати</a>
            </div>
        </div>

    </div><!-- .dlv-content -->

</main>

<?php get_footer(); ?>
