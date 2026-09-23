import {useEffect, useRef, useState} from "react";
import {t} from "@lingui/macro";
import {IconCheck, IconChevronDown} from "@tabler/icons-react";
import {UseFormReturnType} from "@mantine/form";
import {NumberSelector} from "../../../../../common/NumberSelector";

interface QuantitySelectProps {
    formInstance: UseFormReturnType<any>;
    fieldName: string;
    min?: number;
    max?: number;
    selectorSize?: 'default' | 'compact';
    onLimitReached?: () => void;
}

const getQuantityFromFormValue = (values: Record<string, any>, fieldName: string): number => {
    const value = fieldName.split('.').reduce<any>((acc, key) => (acc ? acc[key] : undefined), values);
    return Number(value ?? 0);
};

export const QuantitySelect = ({
                                   formInstance,
                                   fieldName,
                                   min = 0,
                                   max = 100,
                                   selectorSize = 'default',
                                   onLimitReached,
                               }: QuantitySelectProps) => {
    const [open, setOpen] = useState(false);
    const wrapperRef = useRef<HTMLDivElement>(null);
    const quantity = getQuantityFromFormValue(formInstance.values, fieldName);

    useEffect(() => {
        const handlePointerDown = (event: MouseEvent | TouchEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
                setOpen(false);
            }
        };

        document.addEventListener('pointerdown', handlePointerDown);
        return () => document.removeEventListener('pointerdown', handlePointerDown);
    }, []);

    useEffect(() => {
        if (quantity === 0 && open) {
            setOpen(false);
        }
    }, [quantity, open]);

    return (
        <div className="hi-quantity-select" data-size={selectorSize} data-open={open || undefined} ref={wrapperRef}>
            <button
                type="button"
                className="hi-quantity-select-button"
                data-selected={quantity > 0 || undefined}
                aria-haspopup="listbox"
                aria-expanded={open}
                data-testid="product-quantity-select-button"
                onClick={() => setOpen(previous => !previous)}
            >
                {quantity > 0 ? (
                    <>
                        <IconCheck size={16} stroke={2.5}/>
                        {quantity} {t`selected`}
                    </>
                ) : (
                    <>
                        {t`Select`}
                        <IconChevronDown size={16} stroke={2}/>
                    </>
                )}
            </button>

            {open && (
                <div className="hi-quantity-select-dropdown" role="listbox" aria-label={t`Select quantity`}>
                    <div className="hi-quantity-select-dropdown-label">{t`Quantity`}</div>
                    <NumberSelector
                        min={min}
                        max={max}
                        fieldName={fieldName}
                        formInstance={formInstance}
                        selectorSize={selectorSize}
                        onLimitReached={onLimitReached}
                    />
                </div>
            )}
        </div>
    );
};