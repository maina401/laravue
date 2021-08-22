import request from '@/utils/request';

export function userSearch(name) {
  return request({
    url: '/search/user',
    method: 'get',
    params: { name },
  });
}
export function orderSearch(name) {
  return request({
    url: '/search/order',
    method: 'get',
    params: { name },
  });
}
