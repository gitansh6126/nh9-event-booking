import {GenericModalProps, IdParam} from "../../../types.ts";
import {useParams} from "react-router";
import {useGetEvent} from "../../../queries/useGetEvent.ts";
import {useGetOrder} from "../../../queries/useGetOrder.ts";
import {Modal} from "../../common/Modal";
import {Button, CopyButton, LoadingOverlay, Text, Textarea} from "@mantine/core";
import {IconBrandWhatsapp, IconCopy} from "@tabler/icons-react";
import {t} from "@lingui/macro";
import {useEffect, useMemo, useState} from "react";
import {
    buildTicketWhatsAppMessage,
    buildWhatsAppShareUrl
} from "../../../utilites/whatsappTicketMessage.ts";
import classes from "./WhatsAppTicketMessageModal.module.scss";

interface WhatsAppTicketMessageModalProps extends GenericModalProps {
    orderId: IdParam,
}

export const WhatsAppTicketMessageModal = ({onClose, orderId}: WhatsAppTicketMessageModalProps) => {
    const {eventId} = useParams();
    const {data: order} = useGetOrder(eventId, orderId);
    const {data: event} = useGetEvent(eventId);

    const builtMessage = useMemo(
        () => (order && event ? buildTicketWhatsAppMessage(order, event) : ''),
        [order, event],
    );
    const [message, setMessage] = useState<string>(builtMessage);

    useEffect(() => {
        if (builtMessage) {
            setMessage(builtMessage);
        }
    }, [builtMessage]);

    if (!order || !event) {
        return <LoadingOverlay visible/>;
    }

    return (
        <Modal heading={t`Send tickets via WhatsApp`} opened onClose={onClose}>
            <Text className={classes.note}>
                {t`Review the message below, then open WhatsApp to send it to your customer. You can edit the message before sending.`}
            </Text>

            <Textarea
                value={message}
                onChange={(eventChange) => setMessage(eventChange.currentTarget.value)}
                minRows={12}
                autosize
                aria-label={t`WhatsApp message`}
            />

            <div className={classes.actions}>
                <CopyButton value={message}>
                    {({copied, copy}) => (
                        <Button
                            variant="default"
                            leftSection={<IconCopy size={16}/>}
                            onClick={copy}
                        >
                            {copied ? t`Copied` : t`Copy message`}
                        </Button>
                    )}
                </CopyButton>

                <Button
                    component="a"
                    href={buildWhatsAppShareUrl(message)}
                    target="_blank"
                    rel="noopener noreferrer"
                    color="green"
                    leftSection={<IconBrandWhatsapp size={16}/>}
                >
                    {t`Open WhatsApp`}
                </Button>
            </div>
        </Modal>
    )
};