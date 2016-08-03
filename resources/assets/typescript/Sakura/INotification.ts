namespace Sakura
{
    export interface INotification
    {
        id: number;
        user: number;
        time: number;
        read: boolean;
        title: string;
        text: string;
        link: string;
        image: string;
        timeout: number;
    }
}
