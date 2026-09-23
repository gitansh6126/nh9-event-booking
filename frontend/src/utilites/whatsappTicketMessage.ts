/* eslint-disable lingui/no-unlocalized-strings */
import {Event, Order} from "../types.ts";
import {getConfig} from "./config.ts";
import {prettyDate} from "./dates.ts";

const getFrontendBaseUrl = (): string => getConfig('VITE_FRONTEND_URL', '') || '';

export const getAttendeeTicketUrl = (eventId: Event['id'], attendeeShortId: string): string =>
    `${getFrontendBaseUrl()}/product/${eventId}/${attendeeShortId}`;

const getProductNameForAttendee = (order: Order, productId: number, fallback?: string): string =>
    order.order_items?.find(item => Number(item.product_id) === Number(productId))?.item_name
    || fallback
    || 'Ticket';

export const buildTicketWhatsAppMessage = (order: Order, event: Event): string => {
    const activeAttendees = (order.attendees ?? []).filter(attendee =>
        attendee.status === 'ACTIVE' && Boolean(attendee.short_id));

    const lines: string[] = [
        `Hi ${order.first_name}! 👋`,
        '',
        `Here are your tickets for ${event.title}: 🎟️`,
        '',
        `📅 ${prettyDate(event.start_date, event.timezone, true)}`,
        '',
        'Your tickets:',
    ];

    activeAttendees.forEach((attendee, index) => {
        const name = [attendee.first_name, attendee.last_name].filter(Boolean).join(' ').trim();
        const productName = getProductNameForAttendee(order, attendee.product_id, attendee.product?.title);
        lines.push(`${index + 1}. ${name} — ${productName}`);
        lines.push(`   View your ticket: ${getAttendeeTicketUrl(event.id, attendee.short_id)}`);
    });

    lines.push('', `Order Ref: ${order.public_id}`, '', "We can't wait to see you there! 🎉");

    return lines.join('\n');
};

export const buildWhatsAppShareUrl = (message: string): string =>
    `https://api.whatsapp.com/send?text=${encodeURIComponent(message)}`;